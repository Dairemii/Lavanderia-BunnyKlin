function posSystem(){
    return {
        activeMode: 'sale', showPreConfirmacion: false, showConfirmacion: false, ultimaVenta: null,
        itemModal: { open: false, mode: 'add', category: '', id: null, name: '', price: '' },
        services: [ {id:1, name:'Lavado Normal', price:35}, {id:2, name:'Express Hot', price:55}, {id:3, name:'Delicado', price:45}, {id:4, name:'Secado 30min', price:25} ],
        products: [ {id:6, name:'Detergente 1L', price:45}, {id:7, name:'Suavizante', price:35}, {id:8, name:'Bolsa Eco', price:12} ],
        subscriptions: [], // Se carga dinámicamente
        cart: [],
        
        // Datos para capturar en el momento del cobro
        clienteForm: { nombre: '', telefono: '', inicio: '', fin: '' },

        init() {
            // Cargar suscripciones guardadas para mantener sincronizado
            const planesGuardados = localStorage.getItem('lavanderia_suscripciones');
            if (planesGuardados) {
                this.subscriptions = JSON.parse(planesGuardados);
            } else {
                this.subscriptions = [ {id:9, name:'Suscripción Mensual', price:399}, {id:10, name:'Plan VIP Semestral', price:1999} ];
                this.guardarCatalogos();
            }
        },

        guardarCatalogos() {
            localStorage.setItem('lavanderia_suscripciones', JSON.stringify(this.subscriptions));
        },

        toggleMode(mode) { this.activeMode = this.activeMode === mode ? 'sale' : mode; },
        
        handleItemClick(item, category) {
            if (this.activeMode === 'edit') this.openEditModal(item, category);
            else if (this.activeMode === 'delete') this.openDeleteModal(item, category);
            else this.addToCart(item, category); // Ahora enviamos la categoría al carrito
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
            this.guardarCatalogos(); // Guardamos el cambio
            this.closeModal();
        },

        deleteItem() {
            let targetList = this.itemModal.category === 'services' ? this.services : (this.itemModal.category === 'products' ? this.products : this.subscriptions);
            let idx = targetList.findIndex(i => i.id === this.itemModal.id);
            if (idx !== -1) {
                targetList.splice(idx, 1);
                this.cart = this.cart.filter(c => c.id !== this.itemModal.id);
            }
            this.guardarCatalogos(); // Guardamos el cambio
            this.closeModal();
        },

        addToCart(item, category) {
            let found = this.cart.find(i => i.id === item.id);
            if (found) found.quantity++;
            else this.cart.push({...item, quantity: 1, category: category}); // Guardamos qué tipo de producto es
        },

        updateQty(index, amount) {
            this.cart[index].quantity = (parseInt(this.cart[index].quantity) || 0) + amount;
            if (this.cart[index].quantity <= 0) this.removeItem(index);
        },
        removeItem(index) { this.cart.splice(index, 1); },
        clearCart() { this.cart = []; },
        get total() { return this.cart.reduce((sum, item) => sum + (item.price * (parseFloat(item.quantity) || 0)), 0); },
        formatMoney(amount) { return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(amount); },
        
        // Al darle a cobrar, preparamos las fechas
        checkout() { 
            if(this.cart.length) {
                const today = new Date();
                const nextMonth = new Date(); 
                nextMonth.setDate(today.getDate() + 30); // Por defecto suma 30 días
                
                this.clienteForm = { 
                    nombre: '', 
                    telefono: '', 
                    inicio: today.toISOString().split('T')[0], 
                    fin: nextMonth.toISOString().split('T')[0] 
                };
                this.showPreConfirmacion = true; 
            }
        },
        cancelarCheckout() { this.showPreConfirmacion = false; },

        confirmarCheckout() {
            let historial = JSON.parse(localStorage.getItem('historial_ventas')) || [];
            let numeroTicket = historial.length + 1;
            let folioSecuencial = 'BK-' + numeroTicket.toString().padStart(4, '0');
            
            const nuevaVenta = { 
                id: Date.now(), folio: folioSecuencial,
                fecha: new Date().toLocaleString("es-MX", { timeZone: "America/Mexico_City", day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }),
                total: this.total, metodo: 'Efectivo',
                detalles: JSON.parse(JSON.stringify(this.cart)),
                cliente: this.clienteForm.nombre || 'Público en General'
            };

            historial.unshift(nuevaVenta);
            localStorage.setItem('historial_ventas', JSON.stringify(historial));

            // MAGIA CORREGIDA: ENVIAR EL CLIENTE AL OTRO PANEL SI SE ESCRIBIÓ SU NOMBRE
            if (this.clienteForm.nombre.trim() !== '') {
                let subscripcionComprada = this.cart.find(item => item.category === 'subscriptions');
                let planName = subscripcionComprada ? subscripcionComprada.name : 'Ninguna';
                let prendas = this.cart.filter(i => i.category === 'services').map(i => i.quantity + 'x ' + i.name).join(', ');

                // AHORA SÍ APUNTA A LA BASE DE DATOS CORRECTA (v2)
                let clientes = JSON.parse(localStorage.getItem('lavanderia_clientes_final_v2')) || [];
                clientes.unshift({
                    id: Date.now(),
                    name: this.clienteForm.nombre,
                    phone: this.clienteForm.telefono,
                    items: prendas || 'Solo pago de plan',
                    status: 'Pendiente', // Corregido: Ya dice Pendiente y no Recibido
                    subscription: planName,
                    subscriptionEndDate: planName !== 'Ninguna' ? this.clienteForm.fin : '' // Si no compró plan, no hay fecha
                });
                localStorage.setItem('lavanderia_clientes_final_v2', JSON.stringify(clientes));
            }

            this.ultimaVenta = nuevaVenta;
            this.showPreConfirmacion = false;
            this.showConfirmacion = true;
            this.clearCart();
        },
        cerrarConfirmacion() { this.showConfirmacion = false; }
    }
}