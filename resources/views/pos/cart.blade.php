<div class="bg-white rounded-3xl shadow-[0_10px_40px_rgba(30,85,170,0.08)] border-2 border-slate-100 sticky top-8 flex flex-col h-[calc(100vh-4rem)] overflow-hidden">
    
    {{-- Cabecera --}}
    <div class="p-6 border-b border-[#1E55AA]/10 bg-[#F4F8FC]">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-[#FFE63C] text-[#1E55AA] rounded-xl shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 
                     2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h3 class="text-xl font-black text-[#1E55AA]">Canasta</h3>
            </div>
            <button @click="clearCart()" x-show="cart.length" class="text-xs font-bold text-rose-500 py-1.5 px-3 rounded-lg hover:bg-rose-50 transition-colors">VACIAR</button>
        </div>
    </div>

    {{-- Lista de Artículos --}}
    <div class="flex-1 overflow-y-auto p-4 custom-scrollbar space-y-2 bg-white">
        <template x-if="!cart.length">
            <div class="h-full flex flex-col items-center justify-center text-center opacity-70">
                <div class="w-16 h-16 bg-[#F4F8FC] rounded-full flex items-center justify-center mb-3">
                    <svg class="w-8 h-8 text-[#1E55AA]/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <p class="font-bold text-[#1E55AA]/60 text-lg">Sin artículos</p>
            </div>
        </template>

        <template x-for="(cartItem, index) in cart" :key="cartItem.id">
            <div class="flex items-center gap-2 bg-white p-3 rounded-xl border border-slate-100 shadow-sm hover:border-[#1E55AA]/20 transition-colors">
                <div class="flex-1 pl-1">
                    <p class="font-bold text-sm text-[#1E55AA] leading-tight" x-text="cartItem.name"></p>
                    <p class="text-[#1E55AA]/60 font-black text-sm mt-0.5" x-text="formatMoney(cartItem.price * cartItem.quantity)"></p>
                </div>
                <div class="flex items-center bg-[#F4F8FC] rounded-lg p-0.5 border border-slate-200">
                    <button @click="updateQty(index, -1)" class="w-7 h-7 flex items-center justify-center rounded-md text-[#1E55AA]/60 hover:bg-white hover:text-rose-500 transition-colors shadow-sm active:scale-95">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"/></svg>
                    </button>
                    <input type="text" inputmode="numeric" x-model.number="cartItem.quantity" @change="if(cartItem.quantity <= 0 || isNaN(cartItem.quantity)) removeItem(index)" class="w-8 bg-transparent text-center font-bold text-[#1E55AA] text-sm focus:outline-none border-none p-0 selection:bg-[#FFE63C]">
                    <button @click="updateQty(index, 1)" class="w-7 h-7 flex items-center justify-center rounded-md bg-white text-[#1E55AA] hover:bg-[#1E55AA] hover:text-white transition-colors shadow-sm border border-slate-100 active:scale-95">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                    </button>
                </div>
                <button @click="removeItem(index)" class="p-1.5 bg-rose-50 text-rose-400 hover:text-rose-600 hover:bg-rose-100 rounded-lg transition-colors border border-rose-100 ml-1 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </template>
    </div>

    {{-- Footer de Cobro --}}
    <div class="p-6 bg-[#F4F8FC] border-t border-[#1E55AA]/10">
        <div class="flex items-center justify-between mb-4 px-1">
            <div class="text-[#1E55AA]/60 font-bold text-xs uppercase tracking-widest">Total a pagar</div>
            <div class="text-3xl font-black text-[#1E55AA]" x-text="formatMoney(total)"></div>
        </div>
        <button @click="checkout()" :disabled="!cart.length" class="w-full py-4 rounded-xl text-lg font-bold transition-colors flex items-center justify-center gap-2" :class="!cart.length ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-[#1E55AA] text-white hover:bg-[#153e7d] shadow-md'">
            <span>Cobrar Ticket</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
    </div>
</div>