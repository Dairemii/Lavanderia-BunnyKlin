@props(['data'])

<div id="calendar-wrapper" class="bg-white dark:bg-gray-900 rounded-lg shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700 transition-colors duration-300">
    <!-- Encabezado del Calendario -->
    <div class="flex items-center justify-between px-6 py-4 bg-gray-800 dark:bg-black text-white">
        <div class="flex items-center space-x-4">
            <h2 class="text-xl font-bold capitalize min-w-[150px]">
                {{ $data['currentMonthName'] }}
            </h2>
            
            <!-- Selector de Año Adaptativo -->
            <select 
                onchange="changeDate({{ $data['currentMonth'] }}, this.value)" 
                class="bg-gray-700 dark:bg-gray-800 text-white text-sm rounded border-none focus:ring-2 focus:ring-blue-500 py-1 px-2 cursor-pointer transition-colors"
            >
                @foreach($data['years'] as $year)
                    <option value="{{ $year }}" {{ $year == $data['currentYear'] ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex space-x-2">
            <!-- Botón Anterior -->
            <button 
                onclick="changeDate({{ $data['prevMonth'] }}, {{ $data['prevYear'] }})" 
                class="p-2 hover:bg-gray-700 dark:hover:bg-gray-600 rounded-full transition"
                title="Mes anterior"
            >
                &larr; Anterior
            </button>

            <!-- Botón Hoy -->
            <button 
                onclick="changeDate({{ \Carbon\Carbon::now()->month }}, {{ \Carbon\Carbon::now()->year }})" 
                class="px-3 py-1 bg-blue-600 hover:bg-blue-500 rounded text-sm transition shadow-sm"
            >
                Hoy
            </button>

            <!-- Botón Siguiente -->
            <button 
                onclick="changeDate({{ $data['nextMonth'] }}, {{ $data['nextYear'] }})" 
                class="p-2 hover:bg-gray-700 dark:hover:bg-gray-600 rounded-full transition"
                title="Mes siguiente"
            >
                Siguiente &rarr;
            </button>
        </div>
    </div>

    <!-- Rejilla del Calendario -->
    <div class="grid grid-cols-7 border-l border-gray-200 dark:border-gray-700">
        <!-- Nombres de los días -->
        @foreach(['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'] as $day)
            <div class="bg-gray-100 dark:bg-gray-800 py-2 text-center text-xs font-bold text-gray-600 dark:text-gray-400 border-r border-b border-gray-200 dark:border-gray-700 uppercase tracking-wide">
                {{ $day }}
            </div>
        @endforeach

        <!-- Espacios en blanco -->
        @for ($i = 0; $i < $data['firstDayOfWeek']; $i++)
            <div class="h-32 bg-gray-50 dark:bg-gray-900/50 border-r border-b border-gray-200 dark:border-gray-700"></div>
        @endfor

        <!-- Días del mes -->
        @for ($day = 1; $day <= $data['daysInMonth']; $day++)
            @php
                // Creamos un string de fecha formato YYYY-MM-DD para comparar fácil
                $dateKey = sprintf('%04d-%02d-%02d', $data['currentYear'], $data['currentMonth'], $day);
            @endphp
            
            <div data-date="{{ $dateKey }}" class="calendar-day h-32 bg-white dark:bg-gray-900 border-r border-b border-gray-200 dark:border-gray-700 p-2 hover:bg-gray-50 dark:hover:bg-gray-800 transition relative">
                <span class="inline-flex items-center justify-center h-8 w-8 text-sm font-medium {{ ($data['isCurrentMonth'] && $day == $data['today']) ? 'bg-blue-600 text-white rounded-full' : 'text-gray-700 dark:text-gray-300' }}">
                    {{ $day }}
                </span>
                
                <!-- Contenedor donde JavaScript inyectará los eventos -->
                <div class="event-container mt-2 space-y-1 overflow-y-auto max-h-20"></div>
            </div>
        @endfor



        <!--FUNCIONAL, COMENTADO DE MOMENTO POR PRUEBAS
        @for ($day = 1; $day <= $data['daysInMonth']; $day++)
            <div class="h-32 bg-white dark:bg-gray-900 border-r border-b border-gray-200 dark:border-gray-700 p-2 hover:bg-blue-50 dark:hover:bg-gray-800 transition-all relative group">
                <span class="inline-flex items-center justify-center h-8 w-8 text-sm font-medium 
                    {{ ($data['isCurrentMonth'] && $day == $data['today']) 
                        ? 'bg-blue-600 text-white rounded-full shadow-md' 
                        : 'text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}">
                    {{ $day }}
                </span>
                
                <div class="mt-2 space-y-1">
                    {{-- Espacio para eventos --}}
                </div>
            </div>
        @endfor
        -->
    </div>
</div>