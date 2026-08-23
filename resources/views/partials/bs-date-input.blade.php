{{--
    Nepali (BS) date input.

    Params:
      name       (required) - form field name; backend receives AD (Y-m-d), unchanged.
      value      (optional) - current AD value (Y-m-d), e.g. old('ot_date', $record->ot_date)
      class      (optional) - extra CSS classes for the visible input
      required   (optional) - bool, adds red asterisk-friendly required attr on hidden input
      placeholder(optional) - placeholder text, defaults to "YYYY-MM-DD (BS)"

    Usage:
      @include('partials.bs-date-input', ['name' => 'ot_date', 'value' => old('ot_date', date('Y-m-d'))])
--}}
@php
    $bsClass = $class ?? 'w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none cursor-pointer bg-white';
    $bsPlaceholder = $placeholder ?? 'YYYY-MM-DD (BS)';
    $bsRequired = $required ?? false;
@endphp
<div class="relative">
    <input type="text"
           id="{{ $name }}_bs"
           class="bs-date-display {{ $bsClass }}"
           placeholder="{{ $bsPlaceholder }}"
           data-ad-target="{{ $name }}_ad"
           {{ $bsRequired ? 'required' : '' }}
           readonly
           autocomplete="off">
    <input type="hidden"
           name="{{ $name }}"
           id="{{ $name }}_ad"
           value="{{ $value ?? '' }}">
</div>
