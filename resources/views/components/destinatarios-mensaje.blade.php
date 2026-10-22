@props(['destinatarios' => ''])

@php
    $texto = (string) $destinatarios;
    $esLargo = mb_strlen($texto) > 100;
    $corto = $esLargo ? (mb_substr($texto, 0, 100) . '...') : $texto;
@endphp

@if($esLargo)
    <span x-data="{ expanded: false, hovered: false }"
          @mouseenter="hovered = true"
          @mouseleave="hovered = false"
          @click.stop="expanded = !expanded"
          class="cursor-pointer transition-all duration-150 inline-block"
          :class="{ 'underline decoration-dotted underline-offset-2': !expanded && !hovered }"
          title="{{ $texto }}"
          {{ $attributes }}>
        <span x-text="(expanded || hovered) ? @js($texto) : @js($corto)">{{ $corto }}</span>
    </span>
@else
    <span {{ $attributes }}>{{ $texto }}</span>
@endif
