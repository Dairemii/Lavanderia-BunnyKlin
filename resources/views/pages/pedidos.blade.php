@extends('layouts.app')

@section('styles')
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
@endsection

@section('content')
<style>
    [x-cloak] { display: none !important; }
    .font-nunito { font-family: 'Nunito', sans-serif; }

    @keyframes fadeIn {
        0% { opacity: 0; transform: scale(0.95); }
        100% { opacity: 1; transform: scale(1); }
    }
    .animate-fade-in { animation: fadeIn 0.2s ease-out forwards; }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 8s ease-in-out infinite; animation-delay: 2s; }

    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<div x-data="ordersManager()" x-cloak class="font-nunito relative min-h-[85vh] bg-[#F4F8FC] text-[#1E55AA] selection:bg-[#FFE63C] selection:text-[#1E55AA] rounded-3xl p-4 md:p-6 2xl:p-10 z-10 overflow-hidden">

    {{-- Fondo Decorativo --}}
    <div class="absolute inset-0 -z-10 pointer-events-none">
        <div class="absolute top-[-10%] left-[-5%] w-[40vw] h-[40vw] rounded-full bg-[#1E55AA]/5 blur-[100px] animate-float"></div>
        <div class="absolute bottom-[-15%] right-[-10%] w-[50vw] h-[50vw] rounded-full bg-[#1E55AA]/10 blur-[120px] animate-float-delayed"></div>
        <div class="absolute top-[20%] right-[15%] w-[25vw] h-[25vw] rounded-full bg-[#FFE63C]/10 blur-[80px] animate-float" style="animation-duration: 7s;"></div>
    </div>

    {{-- Encabezado --}}
    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between relative z-10">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-[#FFE63C] text-[#1E55AA] rounded-xl shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
            <h2 class="text-3xl font-black text-[#1E55AA]">
                Pedidos y Encargos
            </h2>
        </div>
    </div>

    {{-- Controles Superiores --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between relative z-10">
        <div class="relative w-full sm:w-1/2 md:w-1/3">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#1E55AA]/40">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </span>
            <input type="text" x-model="searchQuery" placeholder="Buscar por ticket, nombre o teléfono..."
                class="w-full rounded-2xl border-2 border-slate-100 bg-white py-3.5 pl-12 pr-4 text-[#1E55AA] font-bold shadow-sm focus:border-[#1E55AA] focus:ring-4 focus:ring-[#1E55AA]/10 outline-none transition-all placeholder:text-[#1E55AA]/40">
        </div>

        <button @click="openModal('add')" class="flex items-center justify-center gap-2 rounded-2xl bg-[#1E55AA] px-6 py-3.5 font-black text-white shadow-[0_8px_20px_rgba(30,85,170,0.2)] hover:bg-[#153e7d] hover:-translate-y-0.5 transition-all duration-300">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Nuevo Encargo
        </button>
    </div>

    {{-- Tarjeta de la Tabla --}}
    <div class="bg-white rounded-3xl shadow-[0_10px_40px_rgba(30,85,170,0.08)] border-2 border-slate-100 relative z-10 overflow-hidden">
        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="w-full table-auto text-left whitespace-nowrap">
                <thead>
                    <tr class="bg-[#F4F8FC] border-b border-[#1E55AA]/10">
                        <th class="min-w-[180px] px-6 py-5 font-black text-[#1E55AA] uppercase tracking-wider text-sm">Ticket & Cliente</th>
                        <th class="min-w-[200px] px-6 py-5 font-black text-[#1E55AA] uppercase tracking-wider text-sm">Detalles del Encargo</th>
                        <th class="min-w-[150px] px-6 py-5 font-black text-[#1E55AA] uppercase tracking-wider text-sm text-center">Entrega & Estado</th>
                        <th class="min-w-[150px] px-6 py-5 font-black text-[#1E55AA] uppercase tracking-wider text-sm text-center">Cobro</th>
                        <th class="px-6 py-5 font-black text-[#1E55AA] uppercase tracking-wider text-sm text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="(order, index) in filteredOrders" :key="order.id">
                        <tr class="hover:bg-[#F4F8FC]/50 transition-colors duration-200">

                            <td class="px-6 py-4">
                                <span class="text-xs font-black text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md mb-1 inline-block" x-text="order.ticket"></span>
                                <h5 class="font-black text-lg text-[#1E55AA]" x-text="order.name"></h5>
                                <p class="font-bold text-[#1E55AA]/60 text-sm mt-0.5 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    <span x-text="order.phone || 'Sin teléfono'"></span>
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                <p class="font-black text-[#1E55AA]" x-text="order.service"></p>
                                <p class="font-bold text-[#1E55AA]/60 text-sm mt-0.5 truncate max-w-[250px]" x-text="order.details || 'Sin especificaciones'"></p>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center gap-1.5">
                                    <span class="inline-flex items-center rounded-xl px-3 py-1 text-[11px] font-black uppercase tracking-wider border shadow-sm"
                                          :class="getStatusClass(order.status)"
                                          x-text="order.status"></span>
                                    <span class="font-bold text-xs text-slate-500" x-text="order.deliveryDate ? 'Entregar: ' + order.deliveryDate : 'Sin especificar'"></span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center gap-0.5">
                                    <span class="font-black text-[#1E55AA]" x-text="'Total: ' + formatMoney(order.total)"></span>
                                    <span class="font-bold text-xs text-emerald-500" x-text="'Anticipo: ' + formatMoney(order.advance)"></span>
                                    <span class="font-bold text-xs text-rose-500" x-show="(order.total - order.advance) > 0" x-text="'Resta: ' + formatMoney(order.total - order.advance)"></span>
                                    <span class="font-bold text-xs text-[#1E55AA]/40" x-show="(order.total - order.advance) <= 0">Pagado</span>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openModal('view', order)" class="p-2 rounded-xl text-[#1E55AA]/40 hover:text-[#1E55AA] hover:bg-[#F4F8FC] transition-colors" title="Ver Detalles">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </button>
                                    <button @click="openModal('edit', order)" class="p-2 rounded-xl text-[#1E55AA]/40 hover:text-emerald-500 hover:bg-emerald-50 transition-colors" title="Editar Encargo">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <button @click="deleteOrder(order.id)" class="p-2 rounded-xl text-[#1E55AA]/40 hover:text-rose-500 hover:bg-rose-50 transition-colors" title="Eliminar Encargo">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL DE REGISTRO DE ENCARGO --}}
    <div x-show="isModalOpen" class="fixed inset-0 z-[99999] flex items-center justify-center bg-[#1E55AA]/20 backdrop-blur-sm px-4 py-5 transition-opacity">
        <div class="w-full max-w-3xl max-h-[90vh] overflow-y-auto custom-scrollbar bg-white rounded-3xl shadow-[0_20px_60px_rgba(30,85,170,0.15)] border-2 border-slate-100 p-8 animate-fade-in" @click.stop>

            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                <div class="p-2.5 bg-[#FFE63C] text-[#1E55AA] rounded-xl shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-[#1E55AA]"
                    x-text="modalMode === 'add' ? 'Registrar Encargo' : (modalMode === 'edit' ? 'Editar Encargo' : 'Detalles del Encargo')"></h3>

                <span class="ml-auto bg-[#F4F8FC] px-4 py-1.5 rounded-lg text-[#1E55AA] font-black tracking-widest border border-[#1E55AA]/10" x-text="currentOrder.ticket"></span>
            </div>

            <form @submit.prevent="saveOrder" class="space-y-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-black text-[#1E55AA]">Nombre del Cliente</label>
                        <input type="text" x-model="currentOrder.name" :disabled="modalMode === 'view'" required
                            class="w-full rounded-2xl border-2 border-slate-100 bg-[#F4F8FC] py-3.5 px-4 font-bold text-[#1E55AA] outline-none focus:border-[#1E55AA] focus:bg-white focus:ring-4 focus:ring-[#1E55AA]/10 disabled:opacity-60 transition-all">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-black text-[#1E55AA]">Teléfono</label>
                        <input type="text" x-model="currentOrder.phone" :disabled="modalMode === 'view'" placeholder="Opcional"
                            class="w-full rounded-2xl border-2 border-slate-100 bg-[#F4F8FC] py-3.5 px-4 font-bold text-[#1E55AA] outline-none focus:border-[#1E55AA] focus:bg-white focus:ring-4 focus:ring-[#1E55AA]/10 disabled:opacity-60 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-black text-[#1E55AA]">Tipo de Servicio</label>
                        <select x-model="currentOrder.service" :disabled="modalMode === 'view'"
                            class="w-full rounded-2xl border-2 border-slate-100 bg-[#F4F8FC] py-3.5 px-4 font-bold text-[#1E55AA] outline-none focus:border-[#1E55AA] focus:bg-white focus:ring-4 focus:ring-[#1E55AA]/10 disabled:opacity-60 transition-all appearance-none cursor-pointer">
                            <option value="Lavado por Kilo">Lavado por Kilo</option>
                            <option value="Planchado">Planchado</option>
                            <option value="Edredones / Cobertores">Edredones / Cobertores</option>
                            <option value="Tintorería">Tintorería</option>
                            <option value="Especial">Especial</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-black text-[#1E55AA]">Detalles (Kilos / Piezas / Notas)</label>
                        <input type="text" x-model="currentOrder.details" :disabled="modalMode === 'view'" placeholder=""
                            class="w-full rounded-2xl border-2 border-slate-100 bg-[#F4F8FC] py-3.5 px-4 font-bold text-[#1E55AA] outline-none focus:border-[#1E55AA] focus:bg-white focus:ring-4 focus:ring-[#1E55AA]/10 disabled:opacity-60 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div>
                        <label class="mb-2 block text-sm font-black text-[#1E55AA]">Costo Total ($)</label>
                        <input type="number" step="0.5" min="0" x-model.number="currentOrder.total" :disabled="modalMode === 'view'" required
                            class="w-full rounded-2xl border-2 border-slate-100 bg-[#F4F8FC] py-3.5 px-4 font-black text-[#1E55AA] outline-none focus:border-[#1E55AA] focus:bg-white focus:ring-4 focus:ring-[#1E55AA]/10 disabled:opacity-60 transition-all">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-black text-[#1E55AA]">Adelanto ($)</label>
                        <input type="number" step="0.5" min="0" x-model.number="currentOrder.advance" :disabled="modalMode === 'view'"
                            class="w-full rounded-2xl border-2 border-slate-100 bg-[#F4F8FC] py-3.5 px-4 font-black text-emerald-500 outline-none focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 disabled:opacity-60 transition-all">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-black text-[#1E55AA]">Estado del Lavado</label>
                        <select x-model="currentOrder.status" :disabled="modalMode === 'view'"
                            class="w-full rounded-2xl border-2 border-slate-100 bg-[#F4F8FC] py-3.5 px-3 font-bold text-[#1E55AA] outline-none focus:border-[#1E55AA] focus:bg-white focus:ring-4 focus:ring-[#1E55AA]/10 disabled:opacity-60 transition-all appearance-none cursor-pointer">
                            <option value="Recibido">Recibido</option>
                            <option value="En Proceso">En Proceso</option>
                            <option value="Listo">Listo para Entregar</option>
                            <option value="Entregado">Entregado</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 mt-4">
                    <div>
                        <label class="mb-2 block text-sm font-black text-[#1E55AA]">Fecha de Llegada</label>
                        <input type="date" x-model="currentOrder.arrivalDate" :disabled="modalMode === 'view'" required
                            class="w-full rounded-2xl border-2 border-slate-100 bg-[#F4F8FC] py-3.5 px-3 font-bold text-[#1E55AA] outline-none focus:border-[#1E55AA] focus:bg-white focus:ring-4 focus:ring-[#1E55AA]/10 disabled:opacity-60 transition-all text-[#1E55AA]">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-black text-[#1E55AA]">Fecha de Entrega</label>
                        <input type="date" x-model="currentOrder.deliveryDate" :disabled="modalMode === 'view'"
                            class="w-full rounded-2xl border-2 border-slate-100 bg-[#F4F8FC] py-3.5 px-3 font-bold text-[#1E55AA] outline-none focus:border-[#1E55AA] focus:bg-white focus:ring-4 focus:ring-[#1E55AA]/10 disabled:opacity-60 transition-all text-[#1E55AA]">
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap justify-end gap-3 pt-6 border-t border-slate-100">
                    <button type="button" @click="closeModal" class="rounded-2xl bg-[#F4F8FC] px-8 py-3.5 font-black text-[#1E55AA]/60 hover:bg-slate-200 hover:text-[#1E55AA] transition-all">
                        <span x-text="modalMode === 'view' ? 'Cerrar' : 'Cancelar'"></span>
                    </button>
                    <button x-show="modalMode !== 'view'" type="submit" class="rounded-2xl bg-[#1E55AA] px-10 py-3.5 font-black text-white shadow-[0_8px_20px_rgba(30,85,170,0.2)] hover:bg-[#153e7d] hover:-translate-y-0.5 transition-all"
                        x-text="modalMode === 'add' ? 'Guardar Encargo' : 'Actualizar'"></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('ordersManager', () => ({
            searchQuery: '',
            isModalOpen: false,
            modalMode: 'add',
            orders: [],
            currentOrder: { id: null, ticket: '', name: '', phone: '', service: 'Lavado por Kilo', details: '', total: 0, advance: 0, status: 'Recibido', arrivalDate: '', deliveryDate: '' },

            init() {
                const stored = localStorage.getItem('lavanderia_encargos_v3');
                if (stored) {
                    this.orders = JSON.parse(stored);
                } else {
                    this.orders = [];
                    this.saveToStorage();
                }
            },

            get filteredOrders() {
                if (this.searchQuery === '') return this.orders;
                const q = this.searchQuery.toLowerCase();
                return this.orders.filter(o =>
                    (o.name && o.name.toLowerCase().includes(q)) ||
                    (o.phone && o.phone.toLowerCase().includes(q)) ||
                    (o.ticket && o.ticket.toLowerCase().includes(q))
                );
            },

            getStatusClass(status) {
                switch(status) {
                    case 'Recibido': return 'bg-slate-100 text-slate-600 border-slate-200';
                    case 'En Proceso': return 'bg-[#FFE63C]/30 text-[#1E55AA] border-[#FFE63C]/50';
                    case 'Listo': return 'bg-emerald-50 text-emerald-600 border-emerald-200 shadow-sm';
                    case 'Entregado': return 'bg-blue-50 text-blue-600 border-blue-200';
                    default: return 'bg-slate-100 text-slate-600 border-slate-200';
                }
            },

            formatMoney(amount) {
                return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(amount);
            },

            generateTicket() {
                return 'ORD-' + Math.floor(1000 + Math.random() * 9000);
            },

            openModal(mode, order = null) {
                this.modalMode = mode;
                if (order) {
                    this.currentOrder = { ...order };
                } else {
                    let today = new Date();
                    let formattedToday = today.toISOString().split('T')[0];

                    this.currentOrder = {
                        id: Date.now(),
                        ticket: this.generateTicket(),
                        name: '',
                        phone: '',
                        service: 'Lavado por Kilo',
                        details: '',
                        total: 0,
                        advance: 0,
                        status: 'Recibido',
                        arrivalDate: formattedToday,
                        deliveryDate: ''
                    };
                }
                this.isModalOpen = true;
            },

            closeModal() {
                this.isModalOpen = false;
            },

            saveOrder() {
                if (this.modalMode === 'add') {
                    this.orders.unshift(this.currentOrder);
                } else {
                    const index = this.orders.findIndex(o => o.id === this.currentOrder.id);
                    if (index !== -1) {
                        this.orders.splice(index, 1, this.currentOrder);
                    }
                }
                this.saveToStorage();
                this.closeModal();
            },

            deleteOrder(id) {
                if (confirm('¿Estás seguro de que deseas eliminar este encargo?')) {
                    this.orders = this.orders.filter(o => o.id !== id);
                    this.saveToStorage();
                }
            },

            saveToStorage() {
                localStorage.setItem('lavanderia_encargos_v3', JSON.stringify(this.orders));
            }
        }))
    })
</script>
@endsection
