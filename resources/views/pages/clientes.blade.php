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

<div x-data="clientManager()" x-cloak class="font-nunito relative min-h-[85vh] bg-[#F4F8FC] text-[#1E55AA] selection:bg-[#FFE63C] selection:text-[#1E55AA] rounded-3xl p-4 md:p-6 2xl:p-10 z-10 overflow-hidden">
    
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
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <h2 class="text-3xl font-black text-[#1E55AA]">
                Clientes y Suscripciones
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
            <input type="text" x-model="searchQuery" placeholder="Buscar cliente..."
                class="w-full rounded-2xl border-2 border-slate-100 bg-white py-3.5 pl-12 pr-4 text-[#1E55AA] font-bold shadow-sm focus:border-[#1E55AA] focus:ring-4 focus:ring-[#1E55AA]/10 outline-none transition-all placeholder:text-[#1E55AA]/40">
        </div>

        <button @click="openModal('add')" class="flex items-center justify-center gap-2 rounded-2xl bg-[#1E55AA] px-6 py-3.5 font-black text-white shadow-[0_8px_20px_rgba(30,85,170,0.2)] hover:bg-[#153e7d] hover:-translate-y-0.5 transition-all duration-300">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Nuevo Pedido
        </button>
    </div>

    {{-- Tarjeta de la Tabla --}}
    <div class="bg-white rounded-3xl shadow-[0_10px_40px_rgba(30,85,170,0.08)] border-2 border-slate-100 relative z-10 overflow-hidden">
        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="w-full table-auto text-left whitespace-nowrap">
                <thead>
                    <tr class="bg-[#F4F8FC] border-b border-[#1E55AA]/10">
                        <th class="min-w-[150px] px-6 py-5 font-black text-[#1E55AA] uppercase tracking-wider text-sm">Cliente</th>
                        <th class="min-w-[150px] px-6 py-5 font-black text-[#1E55AA] uppercase tracking-wider text-sm">Servicio / Prenda</th>
                        <th class="min-w-[150px] px-6 py-5 font-black text-[#1E55AA] uppercase tracking-wider text-sm text-center">Plan y Vigencia</th>
                        <th class="px-6 py-5 font-black text-[#1E55AA] uppercase tracking-wider text-sm text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="(client, index) in filteredClients" :key="client.id">
                        <tr class="hover:bg-[#F4F8FC]/50 transition-colors duration-200">
                            <td class="px-6 py-4">
                                <h5 class="font-black text-lg text-[#1E55AA]" x-text="client.name"></h5>
                                <p class="font-bold text-[#1E55AA]/60 text-sm mt-0.5" x-text="client.phone"></p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-bold text-[#1E55AA]" x-text="client.items || 'Sin prenda'"></p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="font-black text-sm text-[#1E55AA]" x-text="client.subscription"></span>
                                    
                                    <template x-if="client.subscription !== 'Ninguna'">
                                        <span class="inline-flex items-center rounded-lg border px-2 py-0.5 text-[10px] font-black uppercase tracking-wider shadow-sm"
                                              :class="getSubscriptionStatus(client).class"
                                              x-text="getSubscriptionStatus(client).text"></span>
                                    </template>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openModal('view', client)" class="p-2 rounded-xl text-[#1E55AA]/40 hover:text-[#1E55AA] hover:bg-[#F4F8FC] transition-colors" title="Ver Detalles">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </button>
                                    <button @click="openModal('edit', client)" class="p-2 rounded-xl text-[#1E55AA]/40 hover:text-emerald-500 hover:bg-emerald-50 transition-colors" title="Editar Pedido">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <button @click="deleteClient(client.id)" class="p-2 rounded-xl text-[#1E55AA]/40 hover:text-rose-500 hover:bg-rose-50 transition-colors" title="Eliminar Pedido">
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

    {{-- MODAL --}}
    <div x-show="isModalOpen" class="fixed inset-0 z-[99999] flex items-center justify-center bg-[#1E55AA]/20 backdrop-blur-sm px-4 py-5 transition-opacity">
        <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto custom-scrollbar bg-white rounded-3xl shadow-[0_20px_60px_rgba(30,85,170,0.15)] border-2 border-slate-100 p-8 animate-fade-in" @click.stop>
            
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                <div class="p-2.5 bg-[#FFE63C] text-[#1E55AA] rounded-xl shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-[#1E55AA]" 
                    x-text="modalMode === 'add' ? 'Registrar Cliente' : (modalMode === 'edit' ? 'Editar Información' : 'Detalles del Cliente')"></h3>
            </div>
            
            <form @submit.prevent="saveClient" class="space-y-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-black text-[#1E55AA]">Nombre del Cliente</label>
                        <input type="text" x-model="currentClient.name" :disabled="modalMode === 'view'" required 
                            class="w-full rounded-2xl border-2 border-slate-100 bg-[#F4F8FC] py-3.5 px-4 font-bold text-[#1E55AA] outline-none focus:border-[#1E55AA] focus:bg-white focus:ring-4 focus:ring-[#1E55AA]/10 disabled:opacity-60 transition-all">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-black text-[#1E55AA]">Teléfono</label>
                        <input type="text" x-model="currentClient.phone" :disabled="modalMode === 'view'" placeholder="427 123 4567" 
                            class="w-full rounded-2xl border-2 border-slate-100 bg-[#F4F8FC] py-3.5 px-4 font-bold text-[#1E55AA] outline-none focus:border-[#1E55AA] focus:bg-white focus:ring-4 focus:ring-[#1E55AA]/10 disabled:opacity-60 transition-all">
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-black text-[#1E55AA]">¿Qué prenda dejó?</label>
                    <input type="text" x-model="currentClient.items" :disabled="modalMode === 'view'" placeholder="Ej. 2 Edredones" 
                        class="w-full rounded-2xl border-2 border-slate-100 bg-[#F4F8FC] py-3.5 px-4 font-bold text-[#1E55AA] outline-none focus:border-[#1E55AA] focus:bg-white focus:ring-4 focus:ring-[#1E55AA]/10 disabled:opacity-60 transition-all">
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-black text-[#1E55AA]">Plan de Suscripción</label>
                        <select x-model="currentClient.subscription" :disabled="modalMode === 'view'"
                            @change="
                                if($event.target.value !== 'Ninguna' && !currentClient.subscriptionEndDate) { 
                                    let d = new Date(); d.setDate(d.getDate() + 30); 
                                    currentClient.subscriptionEndDate = d.toISOString().split('T')[0]; 
                                } else if($event.target.value === 'Ninguna') { 
                                    currentClient.subscriptionEndDate = ''; 
                                }"
                            class="w-full rounded-2xl border-2 border-slate-100 bg-[#F4F8FC] py-3.5 px-4 font-bold text-[#1E55AA] outline-none focus:border-[#1E55AA] focus:bg-white focus:ring-4 focus:ring-[#1E55AA]/10 disabled:opacity-60 transition-all appearance-none cursor-pointer">
                            <option value="Ninguna">Pago Único (Sin Plan)</option>
                            <template x-for="plan in planesPOS" :key="plan.name">
                                <option :value="plan.name" x-text="plan.name"></option>
                            </template>
                        </select>
                    </div>

                    <div x-show="currentClient.subscription !== 'Ninguna'" x-collapse>
                        <label class="mb-2 block text-sm font-black text-[#1E55AA]">Fecha de Vencimiento</label>
                        <input type="date" x-model="currentClient.subscriptionEndDate" :disabled="modalMode === 'view'" 
                            class="w-full rounded-2xl border-2 border-slate-100 bg-[#F4F8FC] py-3.5 px-4 font-bold text-[#1E55AA] outline-none focus:border-[#1E55AA] focus:bg-white focus:ring-4 focus:ring-[#1E55AA]/10 disabled:opacity-60 transition-all text-[#1E55AA]">
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap justify-end gap-3 pt-6 border-t border-slate-100">
                    <button type="button" @click="closeModal" class="rounded-2xl bg-[#F4F8FC] px-8 py-3.5 font-black text-[#1E55AA]/60 hover:bg-slate-200 hover:text-[#1E55AA] transition-all">
                        <span x-text="modalMode === 'view' ? 'Cerrar' : 'Cancelar'"></span>
                    </button>
                    <button x-show="modalMode !== 'view'" type="submit" class="rounded-2xl bg-[#1E55AA] px-10 py-3.5 font-black text-white shadow-[0_8px_20px_rgba(30,85,170,0.2)] hover:bg-[#153e7d] hover:-translate-y-0.5 transition-all" 
                        x-text="modalMode === 'add' ? 'Guardar' : 'Actualizar'"></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('clientManager', () => ({
            searchQuery: '',
            isModalOpen: false,
            modalMode: 'add',
            clients: [],
            planesPOS: [],
            currentClient: { id: null, name: '', phone: '', items: '', status: 'Pendiente', subscription: 'Ninguna', subscriptionEndDate: '' },

            init() {
                // CARGAR PLANES DESDE EL POS
                const planesGuardados = localStorage.getItem('suscripciones_lavanderia') || localStorage.getItem('lavanderia_suscripciones');
                if (planesGuardados) {
                    this.planesPOS = JSON.parse(planesGuardados);
                } else {
                    this.planesPOS = [ 
                        {id:9, name:'Suscripción Mensual', price:399}, 
                        {id:10, name:'Plan VIP Semestral', price:1999} 
                    ];
                }

                // NUEVO NOMBRE DE MEMORIA LOCAL: Esto limpia los errores pasados como "Recibido"
                const stored = localStorage.getItem('lavanderia_clientes_final_v2');
                if (stored) {
                    this.clients = JSON.parse(stored);
                } else {
                    this.clients = [];
                    this.saveToStorage();
                }
            },

            get filteredClients() {
                if (this.searchQuery === '') return this.clients;
                return this.clients.filter(c => 
                    c.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                    c.phone.toLowerCase().includes(this.searchQuery.toLowerCase())
                );
            },

            // LÓGICA CORREGIDA PARA MOSTRAR "ACTIVA (Vence en X días)" y "CADUCADA"
            getSubscriptionStatus(client) {
                if (!client.subscriptionEndDate) return { text: 'Sin fecha', class: 'hidden' };
                
                const today = new Date();
                today.setHours(0,0,0,0);
                const endDate = new Date(client.subscriptionEndDate + 'T12:00:00');
                endDate.setHours(0,0,0,0);

                const diffTime = endDate - today;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                if (diffDays < 0) {
                    return { text: 'CADUCADA', class: 'bg-rose-50 text-rose-600 border-rose-200 shadow-[0_2px_10px_rgba(244,63,94,0.2)]' };
                } else if (diffDays === 0) {
                    return { text: 'ACTIVA (Vence hoy)', class: 'bg-[#FFE63C]/30 text-[#1E55AA] border-[#FFE63C]' };
                } else {
                    return { text: `ACTIVA (Vence en ${diffDays} días)`, class: 'bg-emerald-50 text-emerald-600 border-emerald-200 shadow-sm' };
                }
            },

            openModal(mode, client = null) {
                this.modalMode = mode;
                if (client) {
                    this.currentClient = { ...client };
                } else {
                    this.currentClient = { id: Date.now(), name: '', phone: '', items: '', status: 'Pendiente', subscription: 'Ninguna', subscriptionEndDate: '' };
                }
                this.isModalOpen = true;
            },

            closeModal() {
                this.isModalOpen = false;
            },

            saveClient() {
                if (this.modalMode === 'add') {
                    this.clients.unshift(this.currentClient);
                } else {
                    const index = this.clients.findIndex(c => c.id === this.currentClient.id);
                    if (index !== -1) {
                        this.clients.splice(index, 1, this.currentClient);
                    }
                }
                this.saveToStorage();
                this.closeModal();
            },

            deleteClient(id) {
                if (confirm('¿Estás seguro de que deseas eliminar este registro?')) {
                    this.clients = this.clients.filter(c => c.id !== id);
                    this.saveToStorage();
                }
            },

            saveToStorage() {
                // Usamos el nuevo nombre para no arrastrar errores del pasado
                localStorage.setItem('lavanderia_clientes_final_v2', JSON.stringify(this.clients));
            }
        }))
    })
</script>
@endsection