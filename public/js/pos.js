function posSystem(servicesDb, suppliesDb, subscriptionsDb) {
    const adaptarCatalogo = (data, category) => {
        console.log({ data });
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
        lastSale: null,
        itemModal: {
            open: false,
            mode: "add",
            category: "",
            id: null,
            name: "",
            price: "",
            description: "",
            stock: 0,
            unit: "",
            duration_months: 1,
        },

        clienteForm: { nombre: "", telefono: "", inicio: "", fin: "" },

        services: adaptarCatalogo(servicesDb, "services"),
        supplies: adaptarCatalogo(suppliesDb, "supplies"),
        subscriptions: adaptarCatalogo(subscriptionsDb, "subscriptions"),
        cart: [],

        toggleMode(mode) {
            this.activeMode = this.activeMode === mode ? "sale" : mode;
        },

        handleItemClick(item, category) {
            console.log({ item });
            console.log({ category });
            if (this.activeMode === "edit") this.openEditModal(item, category);
            else if (this.activeMode === "delete")
                this.openDeleteModal(item, category);
            else this.addToCart(item, category);
        },

        openAddModal(category) {
            this.itemModal = {
                open: true,
                mode: "add",
                category: category,
                id: null,
                name: "",
                price: "",
                description: "",
                stock: 0,
                unit: "",
                duration_months: 1,
            };
        },
        openEditModal(item, category) {
            console.log({ item });
            this.itemModal = {
                open: true,
                mode: "edit",
                category: category,
                id: item.id,
                name: item.name,
                price: item.price,
                description: item.description || null,
                stock: item.stock || null,
                unit: item.unit || null,
                duration_months: item.duration_months || null,
            };
        },
        openDeleteModal(item, category) {
            this.itemModal = {
                open: true,
                mode: "delete",
                category: category,
                id: item.id,
                name: item.name,
                price: item.price,
                description: item.description || null,
                stock: item.stock || null,
                unit: item.unit || null,
                duration_months: item.duration_months || null,
            };
        },
        closeModal() {
            this.itemModal.open = false;
        },

        async saveItem() {
            // Validar campos, no vacios
            if (!this.itemModal.name.trim() || this.itemModal.price === "")
                return;

            // Formatear el precio
            let priceVal = parseFloat(this.itemModal.price) || 0;

            // 1. Armamos el "paquete" con toda la info del modal para Laravel
            const payload = {
                id: this.itemModal.id,
                category: this.itemModal.category, // 'services', 'supplies', 'subscriptions'
                name: this.itemModal.name,
                price: priceVal,
                // Campos dinámicos
                description: this.itemModal.description,
                stock: this.itemModal.stock,
                unit: this.itemModal.unit,
                duration_months: this.itemModal.duration_months,
            };

            // Definimos qué lista de Alpine.js vamos a actualizar al terminar
            let targetList =
                this.itemModal.category === "services"
                    ? this.services
                    : this.itemModal.category === "supplies"
                      ? this.supplies
                      : this.subscriptions;

            try {
                if (this.itemModal.mode === "add") {
                    // 2. Disparamos la petición a la nueva ruta limpia del controlador
                    const response = await fetch("/catalogo/guardar", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            // Requerido por seguridad en Laravel
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute("content"),
                        },
                        body: JSON.stringify(payload),
                    });

                    // Si algo falla a nivel de red o servidor (Error 500)
                    if (!response.ok) {
                        const errorCrudo = await response.text();
                        console.error(
                            "🚨 DETALLE DEL ERROR DE LARAVEL:",
                            errorCrudo,
                        );

                        try {
                            const errorJson = JSON.parse(errorCrudo);
                            alert(
                                "Error del servidor: " +
                                    (errorJson.message || errorJson.error),
                            );
                        } catch (e) {
                            alert(
                                "Error crítico del servidor. Revisa la consola.",
                            );
                        }
                        return;
                    }

                    // 3. Laravel nos responde con el elemento creado (Y SU NUEVO ID AUTOGENERADO)
                    const data = await response.json();

                    // 4. Actualizamos el arreglo visual, usando el ID REAL de MySQL
                    targetList.push({
                        id: data.item.id,
                        name: data.item.name,
                        price: parseFloat(data.item.price),
                        category: this.itemModal.category,
                    });
                } else {
                    const response = await fetch("/catalogo/actualizar", {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute("content"),
                        },
                        body: JSON.stringify(payload),
                    });

                    if (!response.ok) throw new Error("Error al actualizar");

                    const data = await response.json();

                    // Encontrar el elemento en la lista visual y actualizarlo
                    let idx = targetList.findIndex(
                        (i) => i.id === this.itemModal.id,
                    );
                    if (idx !== -1) {
                        // Reemplazamos los datos visuales con los que nos devolvió Laravel
                        targetList[idx].name = data.item.name;
                        targetList[idx].price = parseFloat(data.item.price);
                        targetList[idx].description =
                            data.item.description || null;
                        targetList[idx].stock = data.item.stock || null;
                        targetList[idx].unit = data.item.unit || null;
                        targetList[idx].duration_months =
                            data.item.duration_months || null;
                    }
                }

                this.closeModal();
            } catch (error) {
                console.error(error);
                alert("Hubo un problema al intentar guardar el elemento.");
            }
        },

        deleteItem() {
            let targetList =
                this.itemModal.category === "services"
                    ? this.services
                    : this.itemModal.category === "supplies"
                      ? this.supplies
                      : this.subscriptions;
            let idx = targetList.findIndex((i) => i.id === this.itemModal.id);
            if (idx !== -1) {
                targetList.splice(idx, 1);
                this.cart = this.cart.filter((c) => c.id !== this.itemModal.id);
            }
            this.guardarCatalogos(); // Guardamos el cambio
            this.closeModal();
        },

        addToCart(item, category) {
            let found = this.cart.find(
                (i) => i.id === item.id && i.category === category,
            );
            if (found) found.quantity++;
            else this.cart.push({ ...item, category: category, quantity: 1 });
        },

        updateQty(index, amount) {
            this.cart[index].quantity =
                (parseInt(this.cart[index].quantity) || 0) + amount;
            if (this.cart[index].quantity <= 0) this.removeItem(index);
        },

        removeItem(index) {
            this.cart.splice(index, 1);
        },
        clearCart() {
            this.cart = [];
        },
        get total() {
            return this.cart.reduce(
                (sum, item) =>
                    sum + item.price * (parseFloat(item.quantity) || 0),
                0,
            );
        },
        formatMoney(amount) {
            return new Intl.NumberFormat("es-MX", {
                style: "currency",
                currency: "MXN",
            }).format(amount);
        },
        checkout() {
            if (this.cart.length) {
                const today = new Date();
                const nextMonth = new Date();
                nextMonth.setDate(today.getDate() + 30); // Por defecto suma 30 días

                this.clienteForm = {
                    nombre: "",
                    telefono: "",
                    inicio: today.toISOString().split("T")[0],
                    fin: nextMonth.toISOString().split("T")[0],
                };
                this.showPreConfirmacion = true;
            }
        },
        cancelarCheckout() {
            this.showPreConfirmacion = false;
        },

        confirmarCheckout() {
            let historial =
                JSON.parse(localStorage.getItem("historial_ventas")) || [];
            let numeroTicket = historial.length + 1;
            let folioSecuencial =
                "BK-" + numeroTicket.toString().padStart(4, "0");

            const nuevaVenta = {
                id: Date.now(),
                folio: folioSecuencial,
                fecha: new Date().toLocaleString("es-MX", {
                    timeZone: "America/Mexico_City",
                    day: "2-digit",
                    month: "2-digit",
                    year: "numeric",
                    hour: "2-digit",
                    minute: "2-digit",
                    hour12: true,
                }),
                total: this.total,
                metodo: "Efectivo",
                detalles: JSON.parse(JSON.stringify(this.cart)),
                cliente: this.clienteForm.nombre || "Público en General",
            };

            historial.unshift(nuevaVenta);
            localStorage.setItem("historial_ventas", JSON.stringify(historial));

            // MAGIA CORREGIDA: ENVIAR EL CLIENTE AL OTRO PANEL SI SE ESCRIBIÓ SU NOMBRE
            if (this.clienteForm.nombre.trim() !== "") {
                let subscripcionComprada = this.cart.find(
                    (item) => item.category === "subscriptions",
                );
                let planName = subscripcionComprada
                    ? subscripcionComprada.name
                    : "Ninguna";
                let prendas = this.cart
                    .filter((i) => i.category === "services")
                    .map((i) => i.quantity + "x " + i.name)
                    .join(", ");

                // AHORA SÍ APUNTA A LA BASE DE DATOS CORRECTA (v2)
                let clientes =
                    JSON.parse(
                        localStorage.getItem("lavanderia_clientes_final_v2"),
                    ) || [];
                clientes.unshift({
                    id: Date.now(),
                    name: this.clienteForm.nombre,
                    phone: this.clienteForm.telefono,
                    items: prendas || "Solo pago de plan",
                    status: "Pendiente", // Corregido: Ya dice Pendiente y no Recibido
                    subscription: planName,
                    subscriptionEndDate:
                        planName !== "Ninguna" ? this.clienteForm.fin : "", // Si no compró plan, no hay fecha
                });
                localStorage.setItem(
                    "lavanderia_clientes_final_v2",
                    JSON.stringify(clientes),
                );
            }

            this.ultimaVenta = nuevaVenta;
            this.showPreConfirmacion = false;
            this.showConfirmacion = true;
            this.clearCart();
        },

        // async confirmarCheckout() {
        //     try {
        //         const response = await fetch("/ventas", {
        //             method: "POST",
        //             headers: {
        //                 "Content-Type": "application/json",
        //                 Accept: "application/json",
        //                 "X-CSRF-TOKEN": document
        //                     .querySelector('meta[name="csrf-token"]')
        //                     .getAttribute("content"),
        //             },
        //             body: JSON.stringify({
        //                 total: this.total,
        //                 metodo_pago: "Efectivo",
        //                 detalles: this.cart,
        //             }),
        //         });

        //         if (!response.ok)
        //             throw new Error(
        //                 "Error al procesar la venta en el servidor",
        //             );

        //         const data = await response.json();

        //         this.lastSale = {
        //             folio: data.venta.folio,
        //             fecha: new Date(data.venta.created_at).toLocaleString(
        //                 "es-MX",
        //             ),
        //             total: data.venta.total,
        //             detalles: this.cart,
        //         };

        //         this.showPreConfirmacion = false;
        //         this.showConfirmacion = true;
        //         this.clearCart();
        //     } catch (error) {
        //         console.error(error);
        //         alert(
        //             "Hubo un problema al guardar la venta en la base de datos.",
        //         );
        //     }
        // },

        cerrarConfirmacion() {
            this.showConfirmacion = false;
        },
    };
}
