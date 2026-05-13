@extends('layouts.app')

@section('content')
<div x-data="historialSystem()" class="mx-auto max-w-screen-2xl p-4 md:p-6 font-nunito">
    
    {{-- Notificaciones de éxito/error arriba de todo --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {!! session('success') !!}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        {{-- COLUMNA IZQUIERDA: TABLA DE HISTORIAL (Ocupa 8 de 12 columnas) --}}
        <div class="lg:col-span-8">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50">
                    <h2 class="text-xl font-black text-[#1E55AA]">Ventas Registradas</h2>
                </div>
                
                <div class="max-w-full overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-slate-50 text-left text-[#1E55AA] border-b border-slate-100">
                                <th class="py-4 px-4 font-black">Folio</th>
                                <th class="py-4 px-4 font-black">Fecha</th>
                                <th class="py-4 px-4 font-black text-right">Total</th>
                                <th class="py-4 px-4 font-black text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="ventas.length === 0">
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-slate-400 font-bold">No hay ventas para mostrar.</td>
                                </tr>
                            </template>
                            <template x-for="venta in ventas" :key="venta.id">
                                <tr class="border-b border-slate-50 hover:bg-blue-50 transition-colors">
                                    <!-- 'reference' es el folio en tu tabla sales según la imagen común de estos sistemas -->
                                    <td class="py-4 px-4 font-bold text-[#1E55AA]" x-text="venta.reference || venta.id"></td>
                                    
                                    <!-- Formatear la fecha que viene de la base de datos -->
                                    <td class="py-4 px-4 text-sm text-slate-500" x-text="new Date(venta.created_at).toLocaleString('es-MX')"></td>
                                    
                                    <td class="py-4 px-4 font-black text-right text-emerald-600" x-text="formatMoney(venta.total)"></td>
                                    
                                    <td class="py-4 px-4 text-center">
                                        <button 
                                            type="button"
                                            class="text-blue-600 hover:underline font-bold text-xs" 
                                            @click="seleccionarVenta(venta)">
                                            Seleccionar
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: FORMULARIO DE FACTURACIÓN (Ocupa 4 de 12 columnas) --}}
        <div class="lg:col-span-4">
            <div class="bg-white p-6 rounded-2xl shadow-md border border-slate-100 sticky top-6">
                <div class="flex items-center gap-2 mb-6">
                    <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">Datos de Facturación</h2>
                </div>

                <form action="{{ route('venta.facturar') }}" method="POST">
                    @csrf
                    <!-- Pasamos la venta seleccionada como JSON -->
                    <input type="hidden" name="venta_data" :value="JSON.stringify(ventaSeleccionada)">
                    
                    <!-- Mostrar resumen de lo seleccionado (Opcional pero recomendado) -->
                    <template x-if="ventaSeleccionada">
                        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-xl">
                            <p class="text-xs text-blue-600 font-bold uppercase">Venta Seleccionada:</p>
                            <p class="text-sm font-black text-slate-700" x-text="'Folio: ' + ventaSeleccionada.reference"></p>
                            <p class="text-sm text-emerald-600 font-bold" x-text="'Total: ' + formatMoney(ventaSeleccionada.total)"></p>
                        </div>
                    </template>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nombre Legal / Razón Social</label>
                            <input type="text" name="legal_name" class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="Ej. Juan Pérez" required>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">RFC</label>
                            <input type="text" name="tax_id" class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="XAXX010101000" required>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Régimen Fiscal</label>
                            <select name="tax_system" class="w-full border border-slate-300 rounded-xl p-2.5 bg-white">
                                <option value="601" selected>601 - General de Ley Personas Morales</option>
                                <option value="603">603 - Personas Morales con Fines no Lucrativos</option>
                                <option value="605">605 - Sueldos y Salarios</option>
                                <option value="606">606 - Arrendamiento</option>
                                <option value="611">611 - Ingresos por Dividendos</option>
                                <option value="612">612 - Personas Físicas con Actividades Empresariales</option>
                                <option value="616">616 - Sin obligaciones fiscales (Público General)</option>
                                <option value="626">626 - RESICO</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Uso de CFDI</label>
                            <select name="use_cfdi" class="w-full border border-slate-300 rounded-xl p-2.5 bg-white">
                                <option value="G03" selected>G03 - Gastos en general</option>
                                <option value="S01">S01 - Sin efectos fiscales</option>
                                <option value="CP01">CP01 - Pagos</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            {{-- Método de Pago --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Método de Pago</label>
                                <select name="payment_method" class="w-full border border-slate-300 rounded-xl p-2.5 bg-white">
                                    <option value="PUE" selected>PUE - Una sola exhibición</option>
                                    <option value="PPD">PPD - Parcialidades o Diferido</option>
                                </select>
                            </div>
                            {{-- Forma de Pago (Tú lo llamas Formato de pago) --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Forma de Pago</label>
                                <select name="payment_form" class="w-full border border-slate-300 rounded-xl p-2.5 bg-white">
                                    <option value="01">01 - Efectivo</option>
                                    <option value="03">03 - Transferencia Electrónica de Fondos</option>
                                    <option value="04">04 - Tarjeta de Crédito</option>
                                    <option value="08">08 - Vales de Despensa</option>
                                    <option value="28">28 - Tarjeta de Débito</option>
                                    <option value="99">99 - Por definir</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" class="w-full border border-slate-300 rounded-xl p-2.5" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Código Postal</label>
                                <input type="text" name="zip" class="w-full border border-slate-300 rounded-xl p-2.5" required>
                            </div>
                        </div>

                        <button 
                            type="submit" 
                            :disabled="!ventaSeleccionada"
                            :class="!ventaSeleccionada ? 'bg-slate-300 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                            class="w-full text-white font-bold py-3 rounded-xl transition-all shadow-lg"
                        >
                            <span x-show="ventaSeleccionada">Generar Factura</span>
                            <span x-show="!ventaSeleccionada">Seleccione una venta de la tabla</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function historialSystem() {
        return {
            ventas: [],
            ventaSeleccionada: null,
            cargando: true,

            async init() {
                try {
                    // Llamamos a la ruta que ya tienes definida
                    const response = await fetch('/ventas/api-historial');
                    if (!response.ok) throw new Error('Error al obtener datos');
                    
                    this.ventas = await response.json();
                } catch (error) {
                    console.error("Error cargando el historial:", error);
                    // Opcional: Fallback al localStorage si falla la red
                    this.ventas = JSON.parse(localStorage.getItem('historial_ventas')) || [];
                } finally {
                    this.cargando = false;
                }
            },

            seleccionarVenta(venta) {
                // Mapeamos los datos para que el controlador de factura los entienda
                // Si en tu base de datos la relación se llama 'items', 
                // la convertimos a 'detalles' para que tu FacturaController no falle.
                this.ventaSeleccionada = {
                    ...venta,
                    detalles: venta.items || venta.detalles // Asegura compatibilidad
                };
            },

            formatMoney(amount) {
                return '$' + Number(amount).toLocaleString('es-MX', { 
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2 
                });
            }
        }
    }
</script>
@endsection