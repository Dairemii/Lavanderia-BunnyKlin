<div class="relative" x-data="{
    dropdownOpen: false,
    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
    },
    closeDropdown() {
        this.dropdownOpen = false;
    }
}" @click.away="closeDropdown()">
    <button
        class="flex items-center text-[#1E55AA] dark:text-gray-400 group"
        @click.prevent="toggleDropdown()"
        type="button"
    >
        <span class="block mr-2 font-black text-sm uppercase tracking-wider">Admin</span>

        <svg
            class="w-4 h-4 transition-transform duration-200 text-[#1E55AA]/50"
            :class="{ 'rotate-180 text-[#1E55AA]': dropdownOpen }"
            fill="none"
            stroke="currentColor"
            stroke-width="3"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-3 flex w-[220px] flex-col rounded-2xl border border-gray-100 bg-white p-3 shadow-xl dark:border-gray-800 dark:bg-gray-dark z-50"
        style="display: none;"
    >
        <div class="px-3 py-2 bg-[#F4F8FC] rounded-xl mb-2">
            <span class="block font-black text-[#1E55AA] text-xs uppercase">Sesión: Admin</span>
        </div>

        <ul class="flex flex-col gap-1 pt-1 pb-2 border-b border-gray-100 dark:border-gray-800">
            @php
                $menuItems = [
                    [
                        'text' => 'Mi Perfil',
                        'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
                        'path' => '#',
                    ],
                    [
                        'text' => 'Configuración',
                        'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>',
                        'path' => '#',
                    ],
                ];
            @endphp

            @foreach ($menuItems as $item)
                <li>
                    <a
                        href="{{ $item['path'] }}"
                        class="flex items-center gap-3 px-3 py-2 font-bold text-gray-600 rounded-lg group text-xs hover:bg-[#F4F8FC] hover:text-[#1E55AA] transition-colors"
                    >
                        <span class="text-gray-400 group-hover:text-[#1E55AA]">
                            {!! $item['icon'] !!}
                        </span>
                        {{ $item['text'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="pt-2">
            <a
                href="/signin"
                class="flex items-center w-full gap-3 px-3 py-2 font-bold text-rose-500 rounded-lg group text-xs hover:bg-rose-50 transition-colors"
                @click="closeDropdown()"
            >
                <span class="text-rose-300 group-hover:text-rose-500">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </span>
                Cerrar Sesión
            </a>
        </div>
    </div>
    </div>