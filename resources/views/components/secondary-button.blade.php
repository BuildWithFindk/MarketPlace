<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white border border-[#dddfe2] rounded-md font-semibold text-sm text-[#050505] shadow-sm hover:bg-[#f0f2f5] focus:outline-none focus:ring-2 focus:ring-[#cce5ff] focus:ring-offset-2 disabled:opacity-25 transition']) }}>
    {{ $slot }}
</button>
