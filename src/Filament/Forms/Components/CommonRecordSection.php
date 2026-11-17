<?php

namespace Novius\LaravelFilamentTranslatable\Filament\Forms\Components;

use Exception;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Novius\LaravelFilamentTranslatable\Filament\Traits\IsTranslatable;

/**
 * The fields of the common record of a translatable model using HasCommonRecord (laravel-translatable), in a section
 * bound to its `commonRecord` relationship. Its changes apply to all the translations.
 *
 * - Creating an original: the create page of the package (CreateRecord) saves the common record from these fields
 *   before the translation, so that the common record gets every required value of the form. Several sections of the
 *   same form are saved together, as Filament does. A hidden or disabled section is not saved: the model then creates
 *   its common record with createCommonRecord().
 * - Creating a translation (`locale_parent_id` filled): the translation shares the common record of its parent. The
 *   section shows a notice instead of its fields, and saves nothing.
 */
class CommonRecordSection extends Section
{
    use IsTranslatable;

    /**
     * Whether the create page saved the common record before the record: the section then does not save it again.
     */
    protected bool $isSavedBeforeRecord = false;

    protected function setUp(): void
    {
        parent::setUp();

        // The heading given to make() is kept
        if ($this->heading === null) {
            $this->heading(trans('laravel-filament-translatable::messages.common_record'));
        }
        $this->description(fn (): string => $this->isCreatingTranslation()
            ? trans('laravel-filament-translatable::messages.common_record_from_parent')
            : trans('laravel-filament-translatable::messages.common_record_help'));
        $this->relationship('commonRecord');
    }

    /**
     * No fields while creating a translation: the common record is the one of its parent.
     *
     * @return array<Component|Action|ActionGroup|string|Htmlable>|Schema
     *
     * @throws Exception
     */
    public function getDefaultChildComponents(): array|Schema
    {
        return $this->isCreatingTranslation() ? [] : parent::getDefaultChildComponents();
    }

    /**
     * Whether Filament would save the relationship of the section: not hidden nor disabled (unless configured
     * otherwise), relationship enabled.
     */
    public function isSavedWithRecord(): bool
    {
        $isDisabled = $this->isDisabled();
        $isHidden = $this->isHidden();
        $isSaved = $this->isSaved();

        if ($isDisabled && ! $this->shouldSaveRelationshipsWhenDisabled() && ! $isSaved) {
            return false;
        }
        if ($isHidden && ! $this->shouldSaveRelationshipsWhenHidden()) {
            return false;
        }
        if (! $isDisabled && ! $isHidden && ! $isSaved) {
            return false;
        }

        return $this->hasRelationship();
    }

    /**
     * Called by the create page once it saved the common record before the record: the fields of the section are bound
     * to it, and the section does not save it again.
     */
    public function savedBeforeRecord(Model $commonRecord): static
    {
        $this->cachedExistingRecord($commonRecord);
        $this->isSavedBeforeRecord = true;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function saveRelationshipsBeforeChildren(): void
    {
        if ($this->isSavedBeforeRecord) {
            // Saved once: a later save of the form updates the common record
            $this->isSavedBeforeRecord = false;

            return;
        }

        if (! $this->isCreatingTranslation()) {
            parent::saveRelationshipsBeforeChildren();
        }
    }

    /**
     * @throws Exception
     */
    public function saveRelationships(): void
    {
        if (! $this->isCreatingTranslation()) {
            parent::saveRelationships();
        }
    }

    /**
     * Whether the form creates a translation of an existing model: create operation, locale parent id filled.
     *
     * @throws Exception
     */
    public function isCreatingTranslation(): bool
    {
        $container = $this->getContainer();
        if ($container->getOperation() !== 'create') {
            return false;
        }

        $modelClass = $container->getModel();
        $config = is_string($modelClass) ? $this->modelTranslatableConfig($modelClass) : null;
        $column = $config->locale_parent_id_column ?? 'locale_parent_id';
        $statePath = $this->getRootContainer()->getStatePath();

        return filled(data_get($this->getLivewire(), ($statePath !== '' ? $statePath.'.' : '').$column));
    }
}
