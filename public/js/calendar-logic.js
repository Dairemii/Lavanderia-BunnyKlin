function changeDate(month, year) {
    const container = document.getElementById('calendar-container');
    container.style.opacity = '0.5';

    fetch(`${CALENDAR_URL}?month=${month}&year=${year}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => response.json()) // Cambiamos a .json()
        .then(data => {
            // 1. Actualizamos el cuerpo del calendario
            container.innerHTML = data.html;
            container.style.opacity = '1';

            // 2. REPROGRAMAMOS LOS BOTONES SUPERIORES (Lo que faltaba)
            document.getElementById('btn-prev').setAttribute('data-m', data.prevMonth);
            document.getElementById('btn-prev').setAttribute('data-y', data.prevYear);

            document.getElementById('btn-next').setAttribute('data-m', data.nextMonth);
            document.getElementById('btn-next').setAttribute('data-y', data.nextYear);

            // 3. Actualizamos el texto central
            document.getElementById('current-month-display').innerText = data.currentMonthName;

            // 4. Pintamos las fichas de nuevo
            renderEventsFromLocalStorage();
        })
        .catch(error => console.error('Error:', error));
}

function renderEventsFromLocalStorage() {
    const ventas = JSON.parse(localStorage.getItem('historial_ventas')) || [];
    const clientes = JSON.parse(localStorage.getItem('lavanderia_clientes_final_v2')) || [];
    const pedidos = JSON.parse(localStorage.getItem('lavanderia_encargos_v3')) || [];

    // 1. Limpiar SOLO las celdas del calendario
    document.querySelectorAll('.event-container').forEach(el => el.innerHTML = '');
    
    // 2. Delegar TODA la barra lateral a filtrarPorEstructura
    // Esta función ya limpia, ordena y pinta la barra lateral por ti.
    filtrarPorEstructura('todos');

    // 3. PINTAR SOLO EN LAS CELDAS DEL CALENDARIO
    
    // VENTAS
    ventas.forEach(v => {
        const partes = v.fecha.split(',')[0].split('/');
        const fechaKey = `${partes[2]}-${partes[1]}-${partes[0]}`;
        const celda = document.querySelector(`[data-date="${fechaKey}"] .event-container`);
        if (celda) {
            const btn = createBadge(v.cliente, '💰', 'bg-green-100 text-green-700 border-green-200');
            btn.onclick = () => window.dispatchEvent(new CustomEvent('open-calendar-modal', { detail: { titulo: 'Ticket ' + v.folio, cliente: v.cliente, fecha: v.fecha, total: v.total, detalles: v.detalles } }));
            celda.appendChild(btn);
        }
    });

    // SUSCRIPCIONES
    clientes.forEach(c => {
        if (c.subscriptionEndDate) {
            const celda = document.querySelector(`[data-date="${c.subscriptionEndDate}"] .event-container`);
            if (celda) {
                const btn = createBadge(c.name, '✨', 'bg-pink-100 text-pink-700 border-pink-200');
                btn.onclick = () => window.dispatchEvent(new CustomEvent('open-calendar-modal', { detail: { titulo: 'Vencimiento Plan', cliente: c.name, fecha: c.subscriptionEndDate, total: c.subscription, detalles: [{ name: 'Pendiente: ' + c.items, quantity: 1, price: 0 }] } }));
                celda.appendChild(btn);
            }
        }
    });

    // PEDIDOS (Solo en calendario)
    pedidos.forEach(p => {
        if (p.deliveryDate) {
            const celda = document.querySelector(`[data-date="${p.deliveryDate}"] .event-container`);
            if (celda) {
                const btn = createBadge(p.name, '🚚', 'bg-amber-100 text-amber-700 border-amber-200');
                btn.onclick = () => abrirModalPedido(p, 'Entrega');
                celda.appendChild(btn);
            }
            // NOTA: Se eliminó el bloque que pintaba en el sidebar aquí 
            // porque ya lo hace filtrarPorEstructura('todos').
        }
    });
}

function createBadge(text, icon, colors) {
    const btn = document.createElement('button');
    btn.className = `w-full text-left text-[9px] font-bold ${colors} px-2 py-1 rounded-xl border truncate hover:scale-[1.02] transition-transform mb-1 shadow-sm`;
    btn.innerHTML = `${icon} ${text}`;
    return btn;
}

function abrirModalPedido(p, t) {
    window.dispatchEvent(new CustomEvent('open-calendar-modal', {
        detail: { titulo: t + ': ' + p.ticket, cliente: p.name, fechaLlegada: p.arrivalDate || 'No registrada', fechaEntrega: p.deliveryDate || 'Pendiente', total: p.total, detalles: [{ name: p.service, quantity: 1, price: p.total }, { name: 'Estado: ' + p.status, quantity: 1, price: 0 }] }
    }));
}

function filtrarPorEstructura(categoria) {
    const sidebar = document.getElementById('upcoming-events-list');
    if (!sidebar) return;

    // --- PASO 1: LIMPIEZA TOTAL ---
    // Esto borra cualquier tarjeta que se haya quedado pegada de ejecuciones anteriores
    sidebar.innerHTML = ''; 

    const pedidos = JSON.parse(localStorage.getItem('lavanderia_encargos_v3')) || [];
    const clientes = JSON.parse(localStorage.getItem('lavanderia_clientes_final_v2')) || [];
    const hoy = new Date().toISOString().split('T')[0];

    actualizarEstiloBotones(categoria);

    let itemsAmostrar = [];

    // --- PASO 2: RECOPILAR SIN DUPLICADOS ---
    if (categoria === 'todos' || categoria === 'pedidos') {
        pedidos.forEach(p => {
            // Validamos que tenga fecha y sea futura/hoy
            if (p.deliveryDate && p.deliveryDate >= hoy) {
                // Usamos el ticket o un ID único para evitar duplicar por error de storage
                itemsAmostrar.push({ ...p, type: 'pedido', fechaOrden: p.deliveryDate });
            }
        });
    }

    if (categoria === 'todos' || categoria === 'suscripciones') {
        clientes.forEach(c => {
            if (c.subscriptionEndDate && c.subscriptionEndDate >= hoy) {
                itemsAmostrar.push({ ...c, type: 'suscripcion', fechaOrden: c.subscriptionEndDate });
            }
        });
    }

    // --- PASO 3: ORDENAR CRONOLÓGICAMENTE ---
    itemsAmostrar.sort((a, b) => a.fechaOrden.localeCompare(b.fechaOrden));

    // --- PASO 4: RENDERIZAR ---
    if (itemsAmostrar.length === 0) {
        sidebar.innerHTML = `<p class="text-center text-slate-400 py-10 font-medium">No hay eventos próximos.</p>`;
        return;
    }

    itemsAmostrar.forEach(item => {
        agregarTarjetaSidebar(item, item.type);
    });
}

function agregarTarjetaSidebar(item, tipo) {
    const sidebar = document.getElementById('upcoming-events-list');
    const card = document.createElement('div');

    // Configuración según si es pedido o suscripción
    const esPedido = tipo === 'pedido';
    const titulo = esPedido ? item.name : `${item.name} (Plan)`;
    const subtitulo = esPedido ? item.service : `Vence: ${item.subscription}`;
    const fecha = esPedido ? item.deliveryDate : item.subscriptionEndDate;
    const status = esPedido ? item.status : 'Suscrito';
    const colorClass = esPedido
        ? (item.status === 'Listo' ? 'border-l-green-500' : 'border-l-amber-500')
        : 'border-l-purple-500';
    const badgeClass = esPedido
        ? (item.status === 'Listo' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700')
        : 'bg-purple-100 text-purple-700';

    card.className = `group p-5 rounded-[1.5rem] border border-slate-100 dark:border-gray-800 bg-slate-50/50 dark:bg-gray-800/40 hover:bg-white dark:hover:bg-gray-800 hover:shadow-xl transition-all cursor-pointer border-l-4 ${colorClass}`;

    card.innerHTML = `
            <div class="flex justify-between items-start mb-3">
                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wider ${badgeClass}">${status}</span>
                <span class="text-[10px] font-bold text-slate-400 tracking-tighter">${fecha}</span>
            </div>
            <h4 class="font-black text-slate-800 dark:text-white group-hover:text-blue-600 transition-colors">${titulo}</h4>
            <p class="text-xs text-slate-500 font-medium">${subtitulo}</p>
        `;

    // Asignar el evento click para abrir el modal
    card.onclick = () => {
        if (esPedido) {
            abrirModalPedido(item, 'Evento Próximo');
        } else {
            window.dispatchEvent(new CustomEvent('open-calendar-modal', {
                detail: {
                    titulo: 'Vencimiento Plan',
                    cliente: item.name,
                    fechaEntrega: item.subscriptionEndDate,
                    total: item.subscription,
                    detalles: [{ name: 'Membresía Activa', quantity: 1, price: 0 }]
                }
            }));
        }
    };

    sidebar.appendChild(card);
}

function actualizarEstiloBotones(categoria) {
    const botones = document.querySelectorAll('#filtros-sidebar button');
    botones.forEach(btn => {
        // Resetear todos a gris
        btn.classList.remove('bg-slate-900', 'text-white', 'bg-blue-600', 'bg-purple-600');
        btn.classList.add('bg-slate-100', 'text-slate-500');
    });

    // Aplicar color al activo
    const btnActivo = Array.from(botones).find(b => b.getAttribute('onclick').includes(categoria));
    if (btnActivo) {
        btnActivo.classList.remove('bg-slate-100', 'text-slate-500');
        if (categoria === 'todos') btnActivo.classList.add('bg-slate-900', 'text-white');
        if (categoria === 'pedidos') btnActivo.classList.add('bg-blue-600', 'text-white');
        if (categoria === 'suscripciones') btnActivo.classList.add('bg-purple-600', 'text-white');
    }
}

document.addEventListener('DOMContentLoaded', renderEventsFromLocalStorage);