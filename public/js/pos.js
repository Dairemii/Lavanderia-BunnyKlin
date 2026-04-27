function posSystem(){
    return {
        activeMode: 'sale', showPreConfirmacion: false, showConfirmacion: false, ultimaVenta: null,
        itemModal: { open: false, mode: 'add', category: '', id: null, name: '', price: '' },
        services: [ {id:1, name:'Lavado Normal', price:35}, {id:2, name:'Express Hot', price:55}, {id:3, name:'Delicado', price:45}, {id:4, name:'Secado 30min', price:25} ],
        products: [ {id:6, name:'Detergente 1L', price:45}, {id:7, name:'Suavizante', price:35}, {id:8, name:'Bolsa Eco', price:12} ],
        subscriptions: [ {id:9, name:'Suscripción Mensual', price:399}, {id:10, name:'Plan VIP Semestral', price:1999} ],
        cart: [],

        toggleMode(mode) { this.activeMode = this.activeMode === mode ? 'sale' : mode; },
        
        handleItemClick(item, category) {
            if (this.activeMode === 'edit') this.openEditModal(item, category);
            else if (this.activeMode === 'delete') this.openDeleteModal(item, category);
            else this.addToCart(item); 
        },

        openAddModal(category) { this.itemModal = { open: true, mode: 'add', category: category, id: Date.now(), name: '', price: '' }; },
        openEditModal(item, category) { this.itemModal = { open: true, mode: 'edit', category: category, id: item.id, name: item.name, price: item.price }; },
        openDeleteModal(item, category) { this.itemModal = { open: true, mode: 'delete', category: category, id: item.id, name: item.name, price: item.price }; },
        closeModal() { this.itemModal.open = false; },

        saveItem() {
            if (!this.itemModal.name.trim() || this.itemModal.price === '') return;
            let targetList = this.itemModal.category === 'services' ? this.services : (this.itemModal.category === 'products' ? this.products : this.subscriptions);
            let priceVal = parseFloat(this.itemModal.price) || 0;

            if (this.itemModal.mode === 'add') {
                targetList.push({ id: this.itemModal.id, name: this.itemModal.name, price: priceVal });
            } else {
                let idx = targetList.findIndex(i => i.id === this.itemModal.id);
                if (idx !== -1) { targetList[idx].name = this.itemModal.name; targetList[idx].price = priceVal; }
            }
            this.closeModal();
        },

        deleteItem() {
            let targetList = this.itemModal.category === 'services' ? this.services : (this.itemModal.category === 'products' ? this.products : this.subscriptions);
            let idx = targetList.findIndex(i => i.id === this.itemModal.id);
            if (idx !== -1) {
                targetList.splice(idx, 1);
                this.cart = this.cart.filter(c => c.id !== this.itemModal.id);
            }
            this.closeModal();
        },

        addToCart(item) {
            let found = this.cart.find(i => i.id === item.id);
            if (found) found.quantity++;
            else this.cart.push({...item, quantity: 1});
        },

        updateQty(index, amount) {
            this.cart[index].quantity = (parseInt(this.cart[index].quantity) || 0) + amount;
            if (this.cart[index].quantity <= 0) this.removeItem(index);
        },
        removeItem(index) { this.cart.splice(index, 1); },
        clearCart() { this.cart = []; },
        get total() { return this.cart.reduce((sum, item) => sum + (item.price * (parseFloat(item.quantity) || 0)), 0); },
        formatMoney(amount) { return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(amount); },
        checkout() { if(this.cart.length) this.showPreConfirmacion = true; },
        cancelarCheckout() { this.showPreConfirmacion = false; },

        confirmarCheckout() {
            let historial = JSON.parse(localStorage.getItem('historial_ventas')) || [];
            let numeroTicket = historial.length + 1;
            let folioSecuencial = 'BK-' + numeroTicket.toString().padStart(4, '0');
            
            const nuevaVenta = { 
                id: Date.now(), folio: folioSecuencial,
                fecha: new Date().toLocaleString("es-MX", { timeZone: "America/Mexico_City", day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }),
                total: this.total, metodo: 'Efectivo',
                detalles: JSON.parse(JSON.stringify(this.cart)) 
            };

            historial.unshift(nuevaVenta);
            localStorage.setItem('historial_ventas', JSON.stringify(historial));

            this.ultimaVenta = nuevaVenta;
            this.showPreConfirmacion = false;
            this.showConfirmacion = true;
            this.clearCart();
        },
        cerrarConfirmacion() { this.showConfirmacion = false; }
    }
}