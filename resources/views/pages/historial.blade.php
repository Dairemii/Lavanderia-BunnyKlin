    @extends('layouts.app')

    @section('content')

    <style>
        @media screen {
            #zona-impresion { display: none !important; }
        }
        @media print {
            body * { visibility: hidden; }
            #zona-impresion, #zona-impresion * { visibility: visible; }
            #zona-impresion {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 58mm !important;
                margin: 0 !important;
                padding: 5px !important;
            }
            @page { margin: 0; }
        }
    </style>

    <div x-data="historialSystem()" class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10 font-nunito relative">
        
        {{-- TICKET OCULTO PARA IMPRESIÓN --}}
        <div id="zona-impresion">
            <template x-if="ticketAImprimir">
                <div style="font-family: 'Courier New', monospace; color: #000; background: #fff;">
                    <div style="text-align: center; margin-bottom: 8px;">
                        <h1 style="font-size: 18px; margin: 0; font-weight: 900; letter-spacing: 1px;">BUNNYKLIN</h1>
                        <p style="margin: 2px 0; font-size: 10px;">Ticket de Venta</p>
                    </div>
                    <div style="border-top: 1px dashed #000; margin: 8px 0;"></div>
                    <div style="font-size: 10px; margin: 4px 0;">
                        <div style="display: flex; justify-content: space-between;"><span>Folio:</span> <strong x-text="ticketAImprimir.folio"></strong></div>
                        <div style="display: flex; justify-content: space-between;"><span>Fecha:</span> <span x-text="ticketAImprimir.fecha"></span></div>
                    </div>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 8px;">
                        <thead>
                            <tr>
                                <th style="text-align: left; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 4px 0; font-size: 10px;">CANT/DESC</th>
                                <th style="text-align: right; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 4px 0; font-size: 10px;">IMPORTE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in getDetalles(ticketAImprimir)" :key="item.id">
                                <tr>
                                    <td style="padding: 4px 0; text-align: left; font-size: 10px; border-bottom: 1px dotted #ccc;">
                                        <div style="font-weight: bold; text-transform: uppercase;" x-text="item.name"></div>
                                        <div style="color: #333;" x-text="item.quantity + ' x $' + parseFloat(item.price).toFixed(2)"></div>
                                    </td>
                                    <td style="padding: 4px 0; text-align: right; font-size: 11px; font-weight: bold; vertical-align: bottom; border-bottom: 1px dotted #ccc;" x-text="'$' + (item.price * item.quantity).toFixed(2)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <div style="margin-top: 10px; border-top: 1px dashed #000; padding-top: 8px; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 14px; font-weight: 900;">TOTAL:</span>
                        <span style="font-size: 16px; font-weight: 900;" x-text="'$' + parseFloat(ticketAImprimir.total).toFixed(2)"></span>
                    </div>
                    <div style="text-align: center; font-size: 10px; margin-top: 15px;">
                        <p style="margin: 0;">¡Gracias por su compra!</p>
                        <p style="margin: 0;">*** CONSERVE ESTE TICKET ***</p>
                    </div>
                </div>
            </template>
        </div>

        {{-- MODAL DE CONFIRMACIÓN --}}
        <div x-show="confirmModal.open" class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-[#1E55AA]/40 backdrop-blur-sm" x-transition.opacity.duration.200ms>
            <div class="absolute inset-0" @click="cerrarConfirmacion()"></div>
            <div class="relative bg-white rounded-[2rem] shadow-2xl w-full max-w-sm overflow-hidden animate-fade-in border border-[#1E55AA]/10">
                <div class="p-8 text-center bg-white">
                    <div class="w-16 h-16 bg-[#F4F8FC] rounded-full flex items-center justify-center mx-auto mb-4 text-[#1E55AA] border-2 border-[#1E55AA]/10">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <h2 class="text-xl font-black text-[#1E55AA] mb-2" x-text="confirmModal.title"></h2>
                    <p class="text-[#1E55AA]/60 font-bold mb-8 text-sm" x-text="confirmModal.message"></p>
                    <div class="grid grid-cols-2 gap-3">
                        <button @click="cerrarConfirmacion()" class="py-3 px-4 bg-[#F4F8FC] text-[#1E55AA] font-bold rounded-xl hover:bg-slate-100 transition-colors">Cancelar</button>
                        <button @click="ejecutarConfirmacion()" class="py-3 px-4 bg-rose-500 text-white font-bold rounded-xl hover:bg-rose-600 transition-colors shadow-sm">Sí, eliminar</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- HEADER --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="text-3xl font-black text-[#1E55AA] tracking-tight">Historial de Ventas</h2>
            <button @click="borrarHistorialFiltrado()" x-show="ventasFiltradas.length > 0"
                    class="inline-flex items-center gap-2 bg-white border-2 border-rose-100 text-rose-500 px-4 py-2 rounded-xl text-sm font-black hover:bg-rose-500 hover:border-rose-500 hover:text-white transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span x-text="tipoFiltro === 'dia' ? 'Limpiar ventas del día' : (tipoFiltro === 'mes' ? 'Limpiar ventas del mes' : 'Vaciar todo el historial')"></span>
            </button>
        </div>

        {{-- FILTROS --}}
        <div class="mb-6 bg-white border-2 border-slate-100 rounded-2xl p-4 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Filtrar por:</span>
                <select x-model="tipoFiltro" class="bg-[#F4F8FC] border-2 border-[#1E55AA]/10 text-[#1E55AA] text-sm font-bold rounded-xl px-4 py-2 focus:outline-none focus:border-[#1E55AA] cursor-pointer">
                    <option value="todas">Historial Completo</option>
                    <option value="mes">Mes Específico</option>
                    <option value="dia">Día Exacto</option>
                </select>
                <select x-show="tipoFiltro === 'mes'" x-model="valorFiltro" class="bg-[#FFE63C]/10 border-2 border-[#FFE63C]/50 text-[#1E55AA] text-sm font-bold rounded-xl px-4 py-2 focus:outline-none focus:border-[#FFE63C] cursor-pointer">
                    <template x-for="mes in mesesDisponibles" :key="mes.valor">
                        <option :value="mes.valor" x-text="mes.nombre"></option>
                    </template>
                </select>
                <select x-show="tipoFiltro === 'dia'" x-model="valorFiltro" class="bg-emerald-50 border-2 border-emerald-200 text-emerald-700 text-sm font-bold rounded-xl px-4 py-2 focus:outline-none focus:border-emerald-400 cursor-pointer">
                    <template x-for="dia in diasDisponibles" :key="dia">
                        <option :value="dia" x-text="dia"></option>
                    </template>
                </select>
            </div>
            <div class="inline-flex flex-col items-end rounded-xl bg-success/10 px-5 py-2 border border-success/20">
                <span class="text-[10px] font-black text-success/70 uppercase tracking-widest" x-text="tipoFiltro === 'dia' ? 'Total del Día' : (tipoFiltro === 'mes' ? 'Total del Mes' : 'Total Histórico')"></span>
                <span class="text-xl font-black text-success" x-text="formatMoney(totalFiltro)"></span>
            </div>
        </div>

        {{-- GRID PRINCIPAL --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- TABLA DE HISTORIAL --}}
            <div class="lg:col-span-8">
                <div class="rounded-2xl border border-stroke bg-white px-5 pt-6 pb-2.5 shadow-sm sm:px-7.5 xl:pb-1">
                    <div class="max-w-full overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead>
                                <tr class="bg-gray-2 text-left text-[#1E55AA]">
                                    <th class="py-4 px-4 font-black">Folio</th>
                                    <th class="py-4 px-4 font-black">Fecha</th>
                                    <th class="py-4 px-4 font-black">Servicios</th>
                                    <th class="py-4 px-4 font-black text-right">Total</th>
                                    <th class="py-4 px-4 font-black text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="ventasFiltradas.length === 0">
                                    <tr>
                                        <td colspan="5" class="py-12 text-center text-slate-400 font-bold text-lg" 
                                            x-text="tipoFiltro === 'todas' ? 'Aún no hay ventas registradas.' : 'No hay ventas en esta fecha.'"></td>
                                    </tr>
                                </template>
                                <template x-for="venta in ventasFiltradas" :key="venta.id">
                                    <tr class="border-b border-[#eee] transition-colors cursor-pointer" 
                                        @click="verTicket(venta)"
                                        :class="ticketActivo && ticketActivo.id === venta.id ? 'bg-[#1E55AA]/5 border-l-4 border-l-[#1E55AA]' : 'hover:bg-slate-50 border-l-4 border-l-transparent'">
                                        <td class="py-4 px-4"><span class="text-[#1E55AA] font-black" x-text="venta.folio"></span></td>
                                        <td class="py-4 px-4 text-xs font-bold text-slate-500" x-text="venta.fecha"></td>
                                        <td class="py-4 px-4">
                                            <div class="flex flex-col gap-1">
                                                <template x-for="item in getDetalles(venta)" :key="item.id">
                                                    <div class="text-[11px] font-bold text-slate-600 flex items-center gap-1">
                                                        <div class="w-1 h-1 rounded-full bg-[#FFE63C]"></div>
                                                        <span x-text="item.name"></span> 
                                                        <span class="text-[#1E55AA] bg-white border border-slate-200 px-1 rounded" x-text="'x' + item.quantity"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 font-black text-[#1E55AA] text-right text-lg" x-text="formatMoney(venta.total)"></td>
                                        <td class="py-4 px-4 text-center">
                                            <button @click.stop="borrarVenta(venta.id)" class="inline-flex items-center justify-center rounded-xl bg-rose-50 border-2 border-rose-100 py-1.5 px-3 text-xs font-bold text-rose-500 hover:bg-rose-500 hover:border-rose-500 hover:text-white transition-all shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- VISTA PREVIA DEL TICKET --}}
            <div class="lg:col-span-4 relative">
                <div class="sticky top-8 bg-white border-2 border-slate-100 rounded-[2rem] shadow-sm flex flex-col overflow-hidden h-[calc(100vh-8rem)]">
                    
                    <div class="p-4 bg-[#F4F8FC] border-b border-slate-100 flex items-center gap-3">
                        <div class="p-2 bg-[#1E55AA] text-white rounded-xl shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h3 class="font-black text-[#1E55AA]">Visualizador de Ticket</h3>
                    </div>

                    <div x-show="!ticketActivo" class="flex-1 flex flex-col items-center justify-center text-center p-8 opacity-60">
                        <svg class="w-16 h-16 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
                        <p class="font-bold text-slate-500">Haz clic en una venta para previsualizar el ticket aquí.</p>
                    </div>

                    {{-- TICKET PREVIEW --}}
                    <div x-show="ticketActivo" class="flex-1 overflow-y-auto p-4 custom-scrollbar bg-slate-50 flex justify-center items-start">
                        <div class="bg-white p-4 shadow-sm w-full max-w-[250px] font-mono text-black border border-slate-200">
                            <div class="text-center mb-3">
                                <h1 class="text-lg font-black uppercase tracking-widest mb-1">BUNNYKLIN</h1>
                                <p class="text-[10px]">Ticket de Venta</p>
                            </div>
                            <div class="border-t border-dashed border-slate-400 my-2"></div>
                            <div class="text-[10px] space-y-1 mb-3">
                                <div class="flex justify-between"><span>Folio:</span> <strong x-text="ticketActivo?.folio"></strong></div>
                                <div class="flex justify-between"><span>Fecha:</span> <span x-text="ticketActivo?.fecha"></span></div>
                            </div>
                            <table class="w-full text-[10px]">
                                <thead>
                                    <tr class="border-y border-dashed border-slate-400">
                                        <th class="py-1.5 text-left font-bold">CANT/DESC</th>
                                        <th class="py-1.5 text-right font-bold">IMPORTE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="item in getDetalles(ticketActivo)" :key="item.id">
                                        <tr class="border-b border-dotted border-slate-200">
                                            <td class="py-2 text-left">
                                                <div class="font-bold uppercase" x-text="item.name"></div>
                                                <div class="text-slate-500 mt-0.5" x-text="item.quantity + ' x ' + formatMoney(item.price)"></div>
                                            </td>
                                            <td class="py-2 text-right font-bold align-bottom" x-text="formatMoney(item.price * item.quantity)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <div class="border-t border-dashed border-slate-400 mt-3 pt-3 flex justify-between items-center">
                                <span class="font-bold text-xs">TOTAL:</span>
                                <span class="font-black text-sm" x-text="formatMoney(ticketActivo?.total || 0)"></span>
                            </div>
                            <div class="text-center text-[9px] mt-6 space-y-1 text-slate-600">
                                <p>¡Gracias por su compra!</p>
                                <p>*** CONSERVE ESTE TICKET ***</p>
                            </div>
                        </div>
                    </div>

                    <div x-show="ticketActivo" class="p-4 bg-white border-t border-slate-100">
                        <button @click="imprimirDirecto(ticketActivo)" class="w-full py-3 bg-[#1E55AA] text-white font-black rounded-xl hover:bg-[#153e7d] transition-all shadow-md flex justify-center items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Imprimir Ahora
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function historialSystem() {
        return {
            ventas: [],
            ventasFiltradas: [],
            tipoFiltro: 'todas',
            valorFiltro: '',
            mesesDisponibles: [],
            diasDisponibles: [],
            totalFiltro: 0,
            confirmModal: { open: false, title: '', message: '', onConfirm: null },
            ticketActivo: null,
            ticketAImprimir: null,
            
            init() {
                this.cargarDatos();
                this.$watch('tipoFiltro', (value) => {
                    if (value === 'todas') {
                        this.valorFiltro = '';
                    } else if (value === 'mes' && this.mesesDisponibles.length > 0) {
                        this.valorFiltro = this.mesesDisponibles[0].valor;
                    } else if (value === 'dia' && this.diasDisponibles.length > 0) {
                        this.valorFiltro = this.diasDisponibles[0];
                    }
                    this.filtrarVentas();
                });
                this.$watch('valorFiltro', () => this.filtrarVentas());
            },

            // Función helper para obtener detalles independientemente del formato
            getDetalles(venta) {
                return venta?.detalles || venta?.productos || [];
            },

            cargarDatos() {
                this.ventas = JSON.parse(localStorage.getItem('historial_ventas')) || [];
                this.extraerFechas();
                this.filtrarVentas();
            },

            verTicket(venta) {
                this.ticketActivo = venta;
            },

            imprimirDirecto(venta) {
                this.ticketAImprimir = venta;
                setTimeout(() => window.print(), 150);
            },

            abrirConfirmacion(titulo, mensaje, accion) {
                Object.assign(this.confirmModal, { open: true, title: titulo, message: mensaje, onConfirm: accion });
            },

            cerrarConfirmacion() {
                this.confirmModal.open = false;
                setTimeout(() => { this.confirmModal.onConfirm = null; }, 200);
            },

            ejecutarConfirmacion() {
                if (typeof this.confirmModal.onConfirm === 'function') this.confirmModal.onConfirm();
                this.cerrarConfirmacion();
            },

            borrarVenta(id) {
                this.abrirConfirmacion('¿Eliminar registro?', 'El ticket desaparecerá de tu historial.', () => {
                    this.ventas = this.ventas.filter(v => v.id !== id);
                    if (this.ticketActivo?.id === id) this.ticketActivo = null;
                    this.guardarYActualizar();
                });
            },

            borrarHistorialFiltrado() {
                const mensajes = {
                    dia: `Se eliminarán los tickets del día ${this.valorFiltro}.`,
                    mes: `Se eliminarán los tickets del mes de ${this.valorFiltro}.`,
                    todas: 'Se vaciará por completo el historial de ventas.'
                };
                
                this.abrirConfirmacion('¿Limpiar historial?', mensajes[this.tipoFiltro], () => {
                    const idsABorrar = this.ventasFiltradas.map(v => v.id);
                    this.ventas = this.ventas.filter(v => !idsABorrar.includes(v.id));
                    this.ticketActivo = null;
                    this.tipoFiltro = 'todas';
                    this.guardarYActualizar();
                });
            },

            guardarYActualizar() {
                localStorage.setItem('historial_ventas', JSON.stringify(this.ventas));
                this.cargarDatos();
            },

            extraerFechas() {
                const diasSet = new Set();
                const mesesMap = new Map();

                this.ventas.forEach(v => {
                    const fechaParte = v.fecha.split(',')[0].trim();
                    diasSet.add(fechaParte);
                    
                    const partes = fechaParte.split('/');
                    if (partes.length === 3) {
                        const mesAnio = `${partes[1]}/${partes[2]}`;
                        const fechaObj = new Date(partes[2], parseInt(partes[1]) - 1, 1);
                        let nombreMes = fechaObj.toLocaleString('es-MX', { month: 'long', year: 'numeric' });
                        mesesMap.set(mesAnio, nombreMes.charAt(0).toUpperCase() + nombreMes.slice(1));
                    }
                });

                this.diasDisponibles = Array.from(diasSet);
                this.mesesDisponibles = Array.from(mesesMap).map(([valor, nombre]) => ({ valor, nombre }));
            },

            filtrarVentas() {
                const filtros = {
                    todas: () => this.ventas,
                    dia: () => this.ventas.filter(v => v.fecha.split(',')[0].trim() === this.valorFiltro),
                    mes: () => this.ventas.filter(v => {
                        const partes = v.fecha.split(',')[0].trim().split('/');
                        return `${partes[1]}/${partes[2]}` === this.valorFiltro;
                    })
                };
                this.ventasFiltradas = (filtros[this.tipoFiltro] || filtros.todas)();
                this.totalFiltro = this.ventasFiltradas.reduce((suma, venta) => suma + parseFloat(venta.total), 0);
            },
            
            formatMoney(amount) {
                return '$' + Number(amount).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }
    }
    </script>
    @endsection