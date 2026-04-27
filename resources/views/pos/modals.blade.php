    {{-- MODAL CRUD --}}
    <div x-show="itemModal.open" class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-[#1E55AA]/40 backdrop-blur-sm" x-transition.opacity.duration.200ms>
        <div class="absolute inset-0" @click="closeModal()"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden animate-fade-in border border-[#1E55AA]/10">
            
            <div class="p-6 text-center border-b border-slate-100" :class="itemModal.mode === 'delete' ? 'bg-rose-50' : 'bg-[#F4F8FC]'">
                <h2 class="text-2xl font-black tracking-tight" :class="itemModal.mode === 'delete' ? 'text-rose-700' : 'text-[#1E55AA]'" x-text="itemModal.mode === 'add' ? 'Nuevo Elemento' : (itemModal.mode === 'edit' ? 'Editar Elemento' : 'Confirmar Eliminación')"></h2>
            </div>

            <div class="p-8 bg-white">
                <div x-show="itemModal.mode !== 'delete'" class="space-y-5">
                    <div>
                        <label class="block text-sm font-extrabold text-[#1E55AA]/70 mb-2 ml-1">Nombre</label>
                        <input type="text" x-model="itemModal.name" placeholder="Ej. Lavado Express" class="w-full px-5 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl text-[#1E55AA] font-bold focus:outline-none focus:border-[#1E55AA] focus:bg-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm font-extrabold text-[#1E55AA]/70 mb-2 ml-1">Precio ($ MXN)</label>
                        <input type="text" inputmode="decimal" x-model="itemModal.price" placeholder="0.00" class="w-full px-5 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl text-[#1E55AA] font-bold text-xl focus:outline-none focus:border-[#1E55AA] focus:bg-white transition-colors">
                    </div>
                </div>

                <div x-show="itemModal.mode === 'delete'" class="text-center py-4 bg-rose-50/50 rounded-2xl border-2 border-rose-100">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-3 text-rose-400 border border-rose-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <p class="text-rose-900/70 text-lg mb-2 font-bold">¿Borrar este elemento?</p>
                    <p class="text-2xl font-black text-rose-600" x-text="itemModal.name"></p>
                </div>

                <div class="flex gap-3 mt-8">
                    <button @click="closeModal()" class="flex-1 py-3 px-4 bg-white border-2 border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors">Cancelar</button>
                    <button x-show="itemModal.mode !== 'delete'" @click="saveItem()" class="flex-1 py-3 px-4 bg-[#1E55AA] text-white font-bold rounded-xl hover:bg-[#153e7d] transition-colors shadow-sm">Guardar</button>
                    <button x-show="itemModal.mode === 'delete'" @click="deleteItem()" class="flex-1 py-3 px-4 bg-rose-500 text-white font-bold rounded-xl hover:bg-rose-600 transition-colors shadow-sm">Sí, borrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PRE-CONFIRMACIÓN --}}
    <div x-show="showPreConfirmacion" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-[#1E55AA]/40 backdrop-blur-sm" x-transition.opacity.duration.200ms>
        <div class="relative bg-white rounded-[2rem] shadow-2xl w-full max-w-sm overflow-hidden animate-fade-in border border-[#1E55AA]/10">
            <div class="p-8 text-center bg-white">
                <h2 class="text-2xl font-black text-[#1E55AA] mb-2">¿Confirmar cobro?</h2>
                <p class="text-[#1E55AA]/70 font-bold mb-6">Total a pagar:</p>
                <div class="text-5xl font-black text-[#1E55AA] mb-8" x-text="formatMoney(total)"></div>
                <div class="grid grid-cols-2 gap-4">
                    <button @click="cancelarCheckout()" class="py-3 bg-white border-2 border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors">Cancelar</button>
                    <button @click="confirmarCheckout()" class="py-3 bg-[#FFE63C] text-[#1E55AA] font-bold rounded-xl hover:bg-[#e6cf36] transition-colors shadow-sm">Sí, cobrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- TICKET DE ÉXITO --}}
    <div x-show="showConfirmacion" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-[#1E55AA]/40 backdrop-blur-sm" x-transition.opacity.duration.200ms>
        <div class="relative bg-white rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden animate-fade-in">
            <div class="p-10 text-center bg-white">
                <div class="w-20 h-20 bg-[#F4F8FC] rounded-full flex items-center justify-center mx-auto mb-6 text-[#1E55AA] border-4 border-[#1E55AA]/10">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h2 class="text-3xl font-black text-[#1E55AA] mb-2">¡Venta Lista!</h2>
                <p class="text-[#1E55AA]/70 font-bold mb-8">El cobro ha sido registrado exitosamente.</p>
                <div class="bg-[#F4F8FC] p-6 rounded-2xl text-center mb-8 border border-[#1E55AA]/10">
                    <p class="text-xs font-black text-[#1E55AA]/50 uppercase tracking-widest mb-1">Total Cobrado</p>
                    <p class="text-5xl font-black text-[#1E55AA]" x-text="formatMoney(ultimaVenta?.total || 0)"></p>
                    <p class="text-sm font-bold text-[#1E55AA]/50 mt-3" x-text="'Folio: ' + ultimaVenta?.folio"></p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <button @click="cerrarConfirmacion()" class="py-4 bg-white border-2 border-[#1E55AA]/20 text-[#1E55AA] font-bold rounded-xl hover:bg-[#F4F8FC] transition-colors">Nueva Venta</button>
                    <a href="{{ route('historial') }}" class="py-4 bg-[#1E55AA] text-white font-bold rounded-xl hover:bg-[#153e7d] transition-colors shadow-sm flex items-center justify-center">Ver Ticket</a>
                </div>
            </div>
        </div>
    </div>