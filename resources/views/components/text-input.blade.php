@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-[#dddfe2] focus:border-[#1877f2] focus:ring-[#cce5ff] rounded-md shadow-sm']) }}>
