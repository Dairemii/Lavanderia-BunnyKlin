@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Calendario de Lavandería" />
    
    {{-- Contenedor Alpine.js para manejar el estado del Modal --}}
    <div x-data="{ openDetail: false, info: {} }" 
         @open-calendar-modal.window="info = $event.detail; openDetail = true"
         class="relative mt-6">

        <!-- Contenedor donde se renderizará el calendario -->
        <div id="calendar-container">
            <x-newcalender :data="$data" />
        </div>

        <!-- OVERLAY Y MODAL (Se muestra cuando openDetail es true) -->
        <div x-show="openDetail" 
             x-cloak
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-transition:opacity>
            
            <div @click.away="openDetail = false" 
                 class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-sm w-full overflow-hidden border dark:border-gray-700">
                
                <!-- Cabecera del Modal -->
                <div class="p-4 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 dark:text-white" x-text="info.titulo"></h3>
                    <button @click="openDetail = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                </div>

                <!-- Cuerpo del Modal -->
                <div class="p-4 space-y-4">
                    <div>
                        <p class="text-[10px] text-gray-500 uppercase font-bold tracking-widest">Cliente</p>
                        <p class="text-sm font-medium dark:text-gray-200" x-text="info.cliente"></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-500 uppercase font-bold tracking-widest">Fecha / Referencia</p>
                        <p class="text-sm dark:text-gray-200" x-text="info.fecha"></p>
                    </div>

                    <div>
                        <p class="text-[10px] text-gray-500 uppercase font-bold tracking-widest mb-2">Resumen de Venta</p>
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 border dark:border-gray-700">
                            <template x-for="item in info.detalles">
                                <div class="flex justify-between text-xs mb-1.5 dark:text-gray-300">
                                    <span x-text="item.quantity + 'x ' + item.name"></span>
                                </div>
                            </template>
                            <div class="border-t dark:border-gray-700 mt-2 pt-2 flex justify-between font-bold dark:text-white">
                                <span x-text="typeof info.total === 'number' ? '$' + info.total.toFixed(2) : info.total"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pie del Modal -->
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50">
                    <button @click="openDetail = false" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg font-bold transition shadow-lg shadow-blue-500/30">
                        Cerrar Detalle
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 1. Función principal para cambiar de mes vía AJAX
        function changeDate(month, year) {
            const container = document.getElementById('calendar-container');
            container.style.opacity = '0.5';

            fetch(`{{ route('calendar.index') }}?month=${month}&year=${year}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
                container.style.opacity = '1';
                // RE-RENDERIZAR eventos después de que el HTML nuevo se cargue
                renderEventsFromLocalStorage();
            })
            .catch(error => {
                console.error('Error:', error);
                container.style.opacity = '1';
            });
        }

        // 2. Función para leer LocalStorage y pintar botones en el calendario
        function renderEventsFromLocalStorage() {
            const ventas = JSON.parse(localStorage.getItem('historial_ventas')) || [];
            const clientes = JSON.parse(localStorage.getItem('lavanderia_clientes_final_v2')) || [];

            // Limpiar contenedores de eventos previos
            document.querySelectorAll('.event-container').forEach(el => el.innerHTML = '');

            // PROCESAR VENTAS (Etiquetas Verdes)
            ventas.forEach(venta => {
                const partes = venta.fecha.split(',')[0].split('/'); 
                const fechaKey = `${partes[2]}-${partes[1]}-${partes[0]}`; // YYYY-MM-DD
                const celda = document.querySelector(`[data-date="${fechaKey}"] .event-container`);

                if (celda) {
                    const btn = document.createElement('button');
                    btn.className = "w-full text-left text-[10px] bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300 px-1.5 py-0.5 rounded border border-green-200 dark:border-green-800/50 truncate hover:scale-[1.02] transition-transform mb-1 flex justify-between";
                    btn.innerHTML = `<span>${venta.cliente}</span> <strong>${venta.folio}</strong>`;
                    
                    btn.onclick = () => {
                        window.dispatchEvent(new CustomEvent('open-calendar-modal', { 
                            detail: {
                                titulo: 'Ticket ' + venta.folio,
                                cliente: venta.cliente,
                                fecha: venta.fecha,
                                total: venta.total,
                                detalles: venta.detalles
                            }
                        }));
                    };
                    celda.appendChild(btn);
                }
            });

            // PROCESAR VENCIMIENTOS (Etiquetas Moradas)
            clientes.forEach(cliente => {
                if (cliente.subscriptionEndDate) {
                    const celdaFin = document.querySelector(`[data-date="${cliente.subscriptionEndDate}"] .event-container`);
                    if (celdaFin) {
                        const btnSub = document.createElement('button');
                        btnSub.className = "w-full text-left text-[10px] bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 px-1.5 py-0.5 rounded border border-purple-200 dark:border-purple-800/50 truncate hover:scale-[1.02] transition-transform mb-1";
                        btnSub.innerHTML = `📌 <strong>FIN:</strong> ${cliente.name}`;
                        
                        btnSub.onclick = () => {
                            window.dispatchEvent(new CustomEvent('open-calendar-modal', { 
                                detail: {
                                    titulo: 'Vencimiento de Plan',
                                    cliente: cliente.name,
                                    fecha: 'Vence el: ' + cliente.subscriptionEndDate,
                                    total: cliente.subscription || 'Suscripción activa',
                                    detalles: [{ name: 'Prendas/Servicios: ' + (cliente.items || 'N/A'), quantity: 1, price: 0 }]
                                }
                            }));
                        };
                        celdaFin.appendChild(btnSub);
                    }
                }
            });
        }

        // 3. Inicializar al cargar la página
        document.addEventListener('DOMContentLoaded', renderEventsFromLocalStorage);
    </script>

    {{-- Estilo para evitar parpadeo de Alpine antes de cargar --}}
    <style>
        [x-cloak] { display: none !important; }
    </style>
@endsection