@php
    use Illuminate\Support\Arr;
    use Illuminate\Support\Collection;
    use LaravelLang\Locales\Data\LocaleData;

    /** @var Collection<LocaleData> $locales */

    $trans = $locales->firstWhere('code', $locale)?->localized ?? $locale;
@endphp
<img
    src="{{ asset('vendor/laravel-filament-translatable/images/flags/'.$locale.'.svg') }}"
    title="{{ $trans }}"
    alt="{{ $trans }}"
    class="inline-block"
    width="24"
/>
