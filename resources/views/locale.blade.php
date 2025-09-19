@php
    use Illuminate\Support\Arr;
    use Illuminate\Support\Collection;
    use LaravelLang\Locales\Data\LocaleData;
    use LaravelLang\Locales\Facades\Locales;

    /** @var Collection<LocaleData> $locales */
    $locale = $getState();
    $trans = $locales->firstWhere('code', $locale)?->localized ?? $locale;
@endphp
<img
    src="{{ asset('vendor/laravel-filament-translatable/images/flags/'.$locale.'.svg') }}"
    title="{{ $trans }}"
    alt="{{ $trans }}"
    style="display: inline-block;"
    width="24"
/>
