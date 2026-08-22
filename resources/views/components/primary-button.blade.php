<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#1877f2] border border-transparent rounded-md font-semibold text-sm text-white hover:bg-[#166fe5] focus:bg-[#166fe5] active:bg-[#166fe5] focus:outline-none focus:ring-2 focus:ring-[#cce5ff] focus:ring-offset-2 transition']) }}>
    {{ $slot }}
</button>
