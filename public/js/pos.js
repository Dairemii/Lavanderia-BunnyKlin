function posSystem(servicesDb, suppliesDb, subscriptionsDb) {
    const adaptarCatalogo = (data, category) => {
        return data.map((item) => ({
            id: item.id,
            name: item.name,
            price: parseFloat(item.price),
            category: category,
            description: item.description || null,
            stock: item.stock || null,
            unit: item.unit || null,
            duration_months: item.duration_months || null,
        }));
    };

    return {
        activeMode: "sale",
        showPreConfirmacion: false,
        showConfirmacion: false,
        
        // Estados para la Terminal
        esperandoTerminal: false,
        showErrorModal: false,
        errorPago: "",

        ultimaVenta: null,
        itemModal: { open: false, mode: "add", category: "", id: null, name: "", price: "", description: "", stock: 0, unit: "", duration_months: 1 },
        clienteForm: { nombre: "", telefono: "", inicio: "", fin: "" },
        services: adaptarCatalogo(servicesDb, "services"),
        supplies: adaptarCatalogo(suppliesDb, "supplies"),
        subscriptions: adaptarCatalogo(subscriptionsDb, "subscriptions"),
        cart: [],

        formatMoney(amount) {
            return new Intl.NumberFormat("es-MX", { style: "currency", currency: "MXN" }).format(amount);
        },

        get total() {
            return this.cart.reduce((sum, item) => sum + item.price * (parseFloat(item.quantity) || 0), 0);
        },

        toggleMode(mode) {
            this.activeMode = this.activeMode === mode ? "sale" : mode;
        },

        handleItemClick(item, category) {
            if (this.activeMode === "edit") this.openEditModal(item, category);
            else if (this.activeMode === "delete") this.openDeleteModal(item, category);
            else this.addToCart(item, category);
        },

        addToCart(item, category) {
            let found = this.cart.find((i) => i.id === item.id && i.category === category);
            if (found) found.quantity++;
            else this.cart.push({ ...item, category, quantity: 1 });
        },

        updateQty(index, amount) {
            this.cart[index].quantity = (parseInt(this.cart[index].quantity) || 0) + amount;
            if (this.cart[index].quantity <= 0) this.removeItem(index);
        },

        removeItem(index) { this.cart.splice(index, 1); },
        clearCart() { this.cart = []; },

        // ==========================================
        // LÓGICA DE COBRO
        // ==========================================
        async confirmarCheckout(metodo = "Terminal") {
            if (metodo === "Terminal") {
                if (this.total < 5) {
                    this.mostrarError("El monto mínimo es de $5.00 MXN para procesar tarjeta.");
                    return;
                }

                try {
                    // Ocultar pre-confirmación y mostrar pantalla de carga
                    this.showPreConfirmacion = false;
                    this.esperandoTerminal = true;

                    const response = await fetch("/terminal/cobrar", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                        },
                        body: JSON.stringify({ total: this.total })
                    });

                    if (!response.headers.get("content-type")?.includes("application/json")) {
                        throw new Error("Error interno del servidor al iniciar el cobro.");
                    }

                    const data = await response.json();
                    if (!data.success) throw new Error(data.error || data.mensaje || "Error al conectar con la terminal.");

                    const intentId = data.payment_intent_id;
                    let pagoFinalizado = false;
                    let intentosFallidos = 0;

                    // BUCLE DE ESPERA ACTIVA MEJORADO
                    while (!pagoFinalizado) {
                        await new Promise(resolve => setTimeout(resolve, 3000));

                        try {
                            const statusRes = await fetch(`/terminal/estado/${intentId}`);
                            
                            // Tolerancia a fallos del servidor/red
                            if (!statusRes.headers.get("content-type")?.includes("application/json")) {
                                intentosFallidos++;
                                if(intentosFallidos > 5) throw new Error("Se perdió la conexión prolongada con el servidor.");
                                continue; 
                            }

                            const statusData = await statusRes.json();
                            intentosFallidos = 0; // Reiniciamos contador si hubo éxito de conexión

                            if (statusData.status === 'FINISHED') {
                                pagoFinalizado = true;
                                this.esperandoTerminal = false;
                                this.finalizarVentaLocal("Tarjeta");
                            } 
                            else if (statusData.status === 'CANCELED' || statusData.status === 'ABANDONED' || statusData.status === 'ERROR') {
                                pagoFinalizado = true; 
                                throw new Error("El pago fue cancelado o rechazado directamente en la terminal.");
                            }
                            // Si es OPEN, PROCESSING, UNKNOWN o RETRY, sigue el bucle

                        } catch (pollError) {
                            if (pollError.message.includes("cancelado") || pollError.message.includes("prolongada")) {
                                throw pollError;
                            }
                            intentosFallidos++;
                            if (intentosFallidos > 5) throw new Error("Fallo de red constante. Verifica tu conexión a internet.");
                        }
                    }

                } catch (error) {
                    console.error("Detalle del error:", error);
                    this.esperandoTerminal = false; 
                    this.mostrarError(error.message); 
                }
                return;
            }
            
            // Cobro en Efectivo
            this.finalizarVentaLocal("Efectivo");
        },

        finalizarVentaLocal(metodo) {
            let historial = JSON.parse(localStorage.getItem("historial_ventas")) || [];
            let folio = "BK-" + (historial.length + 1).toString().padStart(4, "0");

            const nuevaVenta = {
                id: Date.now(),
                folio: folio,
                fecha: new Date().toLocaleString("es-MX"),
                total: this.total,
                metodo: metodo,
                detalles: JSON.parse(JSON.stringify(this.cart)),
                cliente: this.clienteForm.nombre || "Público en General",
            };

            historial.unshift(nuevaVenta);
            localStorage.setItem("historial_ventas", JSON.stringify(historial));
            if (this.clienteForm.nombre.trim() !== "") this.registrarClienteLocal();

            this.ultimaVenta = nuevaVenta;
            this.showPreConfirmacion = false;
            this.showConfirmacion = true;
            this.clearCart();
        },

        registrarClienteLocal() {
            let sub = this.cart.find(i => i.category === "subscriptions");
            let clientes = JSON.parse(localStorage.getItem("lavanderia_clientes_final_v2")) || [];
            clientes.unshift({
                id: Date.now(),
                name: this.clienteForm.nombre,
                phone: this.clienteForm.telefono,
                status: "Pendiente",
                subscription: sub ? sub.name : "Ninguna",
            });
            localStorage.setItem("lavanderia_clientes_final_v2", JSON.stringify(clientes));
        },

        // --- MÉTODOS UI ---
        openAddModal(category) { this.itemModal = { open: true, mode: "add", category, id: null, name: "", price: "", description: "", stock: 0, unit: "", duration_months: 1 }; },
        openEditModal(item, category) { this.itemModal = { open: true, mode: "edit", category, id: item.id, name: item.name, price: item.price, description: item.description, stock: item.stock, unit: item.unit, duration_months: item.duration_months }; },
        openDeleteModal(item, category) { this.itemModal = { open: true, mode: "delete", category, ...item }; },
        closeModal() { this.itemModal.open = false; },
        
        checkout() { 
            if (this.cart.length) this.showPreConfirmacion = true; 
        },
        cancelarCheckout() { 
            this.showPreConfirmacion = false; 
        },
        cerrarConfirmacion() { 
            this.showConfirmacion = false; 
        },
        mostrarError(mensaje) {
            this.errorPago = mensaje;
            this.showErrorModal = true;
        },
        cerrarErrorModal() {
            this.showErrorModal = false;
            this.showPreConfirmacion = true;
        }
    };
}