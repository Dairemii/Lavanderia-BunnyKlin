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
                                <tr class="border-b border-slate-50 hover:bg-blue-50 transition-colors cursor-pointer" @click="console.log('Venta seleccionada:', venta)">
                                    <td class="py-4 px-4 font-bold text-[#1E55AA]" x-text="venta.folio"></td>
                                    <td class="py-4 px-4 text-sm text-slate-500" x-text="venta.fecha"></td>
                                    <td class="py-4 px-4 font-black text-right text-emerald-600" x-text="formatMoney(venta.total)"></td>
                                    <td class="py-4 px-4 text-center">
                                        <button class="text-blue-600 hover:underline font-bold text-xs" @click="seleccionarVenta(venta)">Seleccionar</button>
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
                            <p class="text-sm font-black text-slate-700" x-text="'Folio: ' + ventaSeleccionada.folio"></p>
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
                            <select name="tax_system" class="w-full border border-slate-300 rounded-xl p-2.5 bg-white outline-none">
                                <option value="601">General de Ley Personas Morales</option>
                                <option value="605">Sueldos y Salarios</option>
                                <option value="626">Resico</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="correo@ejemplo.com" required>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Código Postal</label>
                            <input type="text" name="zip" class="w-full border border-slate-300 rounded-xl p-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="01000" required>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200 mt-4">
                            Generar Factura CFDI 4.0
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
            ventaSeleccionada: null, // <--- Nueva variable
            init() {
                this.ventas = JSON.parse(localStorage.getItem('historial_ventas')) || [];
            },
            seleccionarVenta(venta) {
                this.ventaSeleccionada = venta;
            },
            formatMoney(amount) {
                return '$' + Number(amount).toLocaleString('es-MX', { minimumFractionDigits: 2 });
            }
        }
    }
</script>
@endsection