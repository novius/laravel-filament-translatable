@php
    /**
     * @var Closure(string): string $localeLabel
     * @var Closure(string): string $flagUrl
     */
    $locale = $getState();
    $trans = $localeLabel($locale);
@endphp
<img
    src="{{ $flagUrl($locale) }}"
    title="{{ $trans }}"
    alt="{{ $trans }}"
    style="display: inline-block;"
    width="24"
/>
