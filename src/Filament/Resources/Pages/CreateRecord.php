<?php

namespace Novius\LaravelFilamentTranslatable\Filament\Resources\Pages;

use Exception;
use Filament\Resources\Pages\CreateRecord as BaseCreateRecord;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Request;
use Novius\LaravelFilamentTranslatable\Filament\Forms\Components\CommonRecordSection;
use Novius\LaravelFilamentTranslatable\Filament\Traits\IsTranslatable;
use Novius\LaravelPublishable\Enums\PublicationStatus;

/**
 * The create page of a translatable model.
 *
 * - With the `locale_parent` and `locale` query parameters, the form creates a translation of the parent, filled by
 *   getDataFromTranslate(): the attributes listed by copiedOnTranslate() (all of them by default), as a draft when the
 *   model is publishable (laravel-publishable) and translationStartsAsDraft() returns true (by default).
 * - With a CommonRecordSection (model using HasCommonRecord), an original saves its common record from the fields of
 *   the section before itself, then itself with its key, in a database transaction: the common record gets every value
 *   of the form, required ones included, and createCommonRecord() of the model is not called.
 */
class CreateRecord extends BaseCreateRecord
{
    use IsTranslatable;

    /**
     * @throws Exception
     */
    public function mount(): void
    {
        $parent = Request::input('locale_parent');
        $locale = Request::input('locale');
        if ($parent && $locale) {
            $model = $this->translatableModel($this->getModel());
            $config = $this->modelTranslatableConfig($this->getModel());
            if ($model !== null && $config !== null && in_array($locale, $config->available_locales, true)) {
                $parent = $model->resolveRouteBinding($parent);
                if ($parent) {
                    $this->data = [
                        ...$this->getDataFromTranslate($parent, $locale),
                        $config->locale_column => $locale,
                        $config->locale_parent_id_column => $parent->getKey(),
                    ];
                }
            }
        }

        parent::mount();
    }

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        if (! empty($this->data)) {
            $this->form->fill($this->data);
        } else {
            $this->form->fill();
        }

        $this->callHook('afterFill');
    }

    /**
     * The attributes of the parent copied to a new translation, or null for all of them.
     *
     * @return array<int, string>|null
     */
    protected function copiedOnTranslate(): ?array
    {
        return null;
    }

    /**
     * Whether a new translation of a publishable model (laravel-publishable) starts as a draft, not published before
     * being reviewed. Return false to copy the publication of the parent instead.
     */
    protected function translationStartsAsDraft(): bool
    {
        return true;
    }

    /**
     * The data of the form creating a translation of the parent: the attributes listed by copiedOnTranslate() (all of
     * them by default), as a draft when the model is publishable and translationStartsAsDraft() returns true.
     *
     * @return array<string, mixed>
     */
    protected function getDataFromTranslate(Model $parent, string $locale): array
    {
        $copied = $this->copiedOnTranslate();
        $data = $copied === null ? $parent->attributesToArray() : $parent->only($copied);

        // The Publishable trait of laravel-publishable
        if ($this->translationStartsAsDraft()
            && method_exists($parent, 'getPublicationStatusColumn')
            && method_exists($parent, 'getPublishedFirstAtColumn')
            && method_exists($parent, 'getPublishedAtColumn')
            && method_exists($parent, 'getExpiredAtColumn')) {
            // Without the publication dates of the parent: with its first publication date, the translation would be
            // saved as unpublished, not as a draft
            $data = [
                ...$data,
                $parent->getPublicationStatusColumn() => PublicationStatus::draft->value,
                $parent->getPublishedFirstAtColumn() => null,
                $parent->getPublishedAtColumn() => null,
                $parent->getExpiredAtColumn() => null,
            ];
        }

        return $data;
    }

    /**
     * Without a CommonRecordSection to save (other models, translations), the creation of Filament. Otherwise, the
     * common record and the record are saved in a transaction: no common record is left alone when the record fails
     * to be saved, even without the database transactions of the panel.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Exception
     */
    protected function handleRecordCreation(array $data): Model
    {
        $record = new ($this->getModel())($data);
        $relation = $this->commonRecordRelationToSaveBefore($record);
        $sections = $relation !== null ? $this->commonRecordSectionsToSaveBefore() : [];
        if ($relation === null || $sections === []) {
            return parent::handleRecordCreation($data);
        }

        return $record->getConnection()->transaction(function () use ($record, $relation, $sections): Model {
            $this->saveCommonRecordBefore($record, $relation, $sections);

            // As BaseCreateRecord::handleRecordCreation(), with the record holding the key of its common record
            if ($parentRecord = $this->getParentRecord()) {
                return $this->associateRecordWithParent($record, $parentRecord);
            }

            $record->save();

            return $record;
        });
    }

    /**
     * The `commonRecord` relationship of a record to create with its common record: none for a model without a
     * `commonRecord` BelongsTo relationship, or a record already holding its common record.
     *
     * @return BelongsTo<Model, Model>|null
     */
    protected function commonRecordRelationToSaveBefore(Model $record): ?BelongsTo
    {
        if (! method_exists($record, 'commonRecord')) {
            return null;
        }

        $relation = $record->commonRecord();
        if (! $relation instanceof BelongsTo || $record->getAttribute($relation->getForeignKeyName()) !== null) {
            return null;
        }

        /** @var BelongsTo<Model, Model> $relation */
        return $relation;
    }

    /**
     * The sections whose common record is saved before the record: none for a translation (it shares the common record
     * of its parent). As Filament, the hidden or disabled sections are not saved: the model then creates the common
     * record with createCommonRecord().
     *
     * @return array<int, CommonRecordSection>
     *
     * @throws Exception
     */
    protected function commonRecordSectionsToSaveBefore(): array
    {
        $sections = array_values(array_filter(
            $this->findCommonRecordSections($this->form),
            static fn (CommonRecordSection $section): bool => $section->isSavedWithRecord(),
        ));

        return $sections === [] || $sections[0]->isCreatingTranslation() ? [] : $sections;
    }

    /**
     * Saves the common record of an original from the fields of the sections, as Filament would, and gives the original
     * its key. The sections then skip their own save of the relationship.
     *
     * @param  BelongsTo<Model, Model>  $relation
     * @param  array<int, CommonRecordSection>  $sections
     */
    protected function saveCommonRecordBefore(Model $record, BelongsTo $relation, array $sections): void
    {
        $data = [];
        foreach ($sections as $section) {
            $data = [...$data, ...($section->getChildSchema()?->getState(shouldCallHooksBefore: false) ?? [])];
        }
        foreach ($sections as $section) {
            $data = $section->mutateRelationshipDataBeforeCreate($data);
        }

        $commonRecord = $relation->getRelated()->newInstance();
        $commonRecord->fill($data)->save();

        $record->setAttribute($relation->getForeignKeyName(), $commonRecord->getAttribute($relation->getOwnerKeyName()));
        $record->setRelation($relation->getRelationName(), $commonRecord);

        foreach ($sections as $section) {
            $section->savedBeforeRecord($commonRecord);
        }
    }

    /**
     * @return array<int, CommonRecordSection>
     */
    protected function findCommonRecordSections(Schema $schema): array
    {
        $sections = [];
        foreach ($schema->getComponents(withActions: false, withHidden: true) as $component) {
            if ($component instanceof CommonRecordSection) {
                $sections[] = $component;

                continue;
            }
            if (! $component instanceof Component) {
                continue;
            }

            foreach ($component->getChildSchemas() as $childSchema) {
                $sections = [...$sections, ...$this->findCommonRecordSections($childSchema)];
            }
        }

        return $sections;
    }
}
