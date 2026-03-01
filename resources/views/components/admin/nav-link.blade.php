@props(['href', 'active' => false])

<a href="{{ $href }}"
   class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors
          {{ $active
              ? 'bg-gray-700 text-white'
              : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
    {{ $slot }}
</a>
