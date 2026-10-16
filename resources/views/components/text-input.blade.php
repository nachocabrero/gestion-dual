@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 focus:border-[#0048FE] focus:ring-2 focus:ring-[#0048FE]/20 rounded-xl shadow-sm text-sm py-2.5 px-3.5']) !!}>
