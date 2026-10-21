import { api } from '../utils/api.js';

const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const money = (value) => `${Number(value || 0).toFixed(2)} Bs`;

export async function setupPedidosModule() {
    const root = document.getElementById('pedidosModule');
    root.innerHTML = `<section class="rounded-lg border border-pink-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3"><div><p class="text-sm font-semibold uppercase tracking-wide text-lime-700">Administracion</p><h2 class="mt-1 text-2xl font-bold text-pink-900">Pedidos</h2></div><button data-create class="rounded-md bg-pink-700 px-4 py-2 font-semibold text-white hover:bg-pink-800">Adicionar pedido</button></div>
        <p data-message class="mb-4 hidden rounded-md px-3 py-2 text-sm" role="status"></p><div data-list></div></section>
        <div data-modal class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-slate-900/50 p-4" role="dialog" aria-modal="true"><section class="my-auto w-full max-w-3xl rounded-lg bg-white p-6 shadow-xl"><div class="mb-5 flex items-center justify-between"><h2 data-title class="text-xl font-bold text-pink-900"></h2><button data-close class="px-2 py-1 text-2xl text-slate-500" aria-label="Cerrar">&times;</button></div>
        <form data-form class="space-y-4"><input type="hidden" name="id"><div class="grid gap-4 sm:grid-cols-2">
        <label class="text-sm font-medium text-slate-700">Estado<select name="Estado" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><option>Pendiente</option><option>En proceso</option><option>Entregado</option><option>Cancelado</option></select></label>
        <label class="text-sm font-medium text-slate-700">Método de pago<select name="Metodo_de_pago" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><option value="">Selecciona un método</option><option value="Efectivo">Efectivo</option><option value="Tarjeta">Tarjeta</option><option value="QR">QR</option></select></label>
        <label class="text-sm font-medium text-slate-700">Cliente<select name="id_cliente" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><option value="">Sin asignar</option></select></label>
        <label data-staff-field class="text-sm font-medium text-slate-700">Responsable<select name="id_personal" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><option value="">Sin asignar</option></select></label>
        <label class="text-sm font-medium text-slate-700">Fecha de entrega<input name="Fecha_entrega" type="datetime-local" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label></div>
        <label class="block text-sm font-medium text-slate-700">Detalle<textarea name="Detalle" maxlength="255" rows="2" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></textarea></label>
        <p data-qr-help class="hidden rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">No hay una cuenta ni un QR oficial configurado. El método se guardará como QR, pero el pago deberá verificarse manualmente; el sistema no confirmará el pago.</p>
        <div><div class="mb-2 flex items-center justify-between"><h3 class="font-semibold text-pink-900">Productos</h3><button type="button" data-add class="rounded-md bg-lime-200 px-3 py-1.5 text-sm font-semibold text-lime-950 hover:bg-lime-300">Añadir producto</button></div>
        <p data-empty class="hidden rounded bg-amber-50 p-3 text-sm text-amber-900">No hay productos registrados. Registra productos en el módulo Productos antes de crear un pedido.</p><div class="mb-1 hidden grid-cols-[minmax(8rem,1fr)_5.5rem_6rem_7rem_auto] gap-2 px-2 text-xs font-semibold text-slate-600 sm:grid"><span>Producto</span><span>Cantidad</span><span class="text-right">Precio unitario</span><span class="text-right">Subtotal</span><span></span></div><div data-items class="space-y-2"></div><div class="mt-3 text-right text-lg font-bold text-pink-900">Total estimado: <span data-total>0.00 Bs</span></div></div>
        <p data-form-message class="hidden text-sm" role="status"></p><div class="flex justify-end gap-2"><button type="button" data-close class="rounded-md border px-4 py-2">Cancelar</button><button data-save class="rounded-md bg-lime-700 px-4 py-2 font-semibold text-white hover:bg-lime-800">Guardar</button></div></form></section></div>
        <style>
            @media print {
                @page { margin: 12mm; }
                body { min-height: 0 !important; background: #fff !important; }
                body * { visibility: hidden !important; }
                [data-ticket-dialog], [data-ticket-dialog] * { visibility: visible !important; }
                [data-ticket-dialog] { display: block !important; position: fixed !important; inset: 0 !important; overflow: visible !important; padding: 0 !important; background: #fff !important; }
                [data-ticket-paper] { width: 100% !important; max-width: none !important; margin: 0 !important; border: 0 !important; box-shadow: none !important; }
                [data-ticket-actions] { display: none !important; }
            }
        </style>
        <div data-ticket-dialog class="fixed inset-0 z-[60] hidden items-center justify-center overflow-y-auto bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="ticket-heading">
            <section data-ticket-paper class="my-auto w-full max-w-xl rounded-xl border border-pink-200 bg-white p-6 shadow-2xl sm:p-8">
                <div data-ticket-actions class="mb-5 flex justify-end gap-2">
                    <button type="button" data-ticket-close class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cerrar</button>
                    <button type="button" data-ticket-print class="rounded-md bg-pink-700 px-4 py-2 text-sm font-semibold text-white hover:bg-pink-800">Imprimir ticket</button>
                </div>
                <div data-ticket-content></div>
            </section>
        </div>
        `;
    const form = root.querySelector('[data-form]'), modal = root.querySelector('[data-modal]'), items = root.querySelector('[data-items]');
    let products = [];
    let productMap = new Map();
    let catalogsReady = false;
    const notify = (msg, error = false) => { const el = root.querySelector('[data-message]'); el.textContent = msg; el.className = `mb-4 rounded-md px-3 py-2 text-sm ${error ? 'bg-pink-100 text-pink-900' : 'bg-lime-100 text-lime-900'}`; };
    const ticketDialog = root.querySelector('[data-ticket-dialog]');
    const ticketContent = root.querySelector('[data-ticket-content]');
    const dateText = (value) => value ? esc(String(value).replace('T', ' ').slice(0, 19)) : 'No registrada';
    const showTicket = async (id) => {
        try {
            const order = await api.get(`pedido/${encodeURIComponent(id)}`);
            const validProducts = Array.isArray(order.productos)
                ? order.productos.filter((product) => product.Nombre && Number.isInteger(Number(product.Cantidad)) && Number(product.Cantidad) > 0)
                : [];
            if (!order.id || !validProducts.length || validProducts.length !== order.productos.length) {
                throw new Error('El pedido no tiene información válida de productos para generar el ticket.');
            }
            const registeredAt = order.Fecha_registro || order.Fecha_creacion || order.created_at;
            ticketContent.innerHTML = `<article class="text-slate-800">
                <header class="border-b-2 border-pink-200 pb-5 text-center">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-lime-800">Pastelería Kivon</p>
                    <h1 id="ticket-heading" class="mt-2 text-2xl font-bold text-pink-950">Ticket de elaboración</h1>
                    <p class="mt-2 text-lg font-semibold text-pink-800">Pedido #${esc(order.id)}</p>
                    <span class="mt-2 inline-flex rounded-full bg-lime-100 px-3 py-1 text-sm font-semibold text-lime-900">${esc(order.Estado || 'Sin estado')}</span>
                </header>
                <dl class="grid grid-cols-1 gap-3 border-b border-slate-200 py-5 sm:grid-cols-2">
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cliente</dt><dd class="mt-1 font-semibold">${esc(order.cliente || 'Sin cliente asignado')}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Registrado</dt><dd class="mt-1">${dateText(registeredAt)}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Entrega</dt><dd class="mt-1 font-semibold">${dateText(order.Fecha_entrega)}</dd></div>
                </dl>
                <section class="py-5">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-pink-900">Productos a elaborar</h2>
                    <ul class="mt-3 divide-y divide-pink-100">${validProducts.map((product) => `<li class="flex items-start justify-between gap-4 py-3"><span class="font-medium">${esc(product.Nombre)}</span><span class="shrink-0 rounded-md bg-pink-50 px-3 py-1 font-bold text-pink-900">× ${esc(product.Cantidad)}</span></li>`).join('')}</ul>
                </section>
                <section class="border-t border-slate-200 pt-4">
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Observaciones</h2>
                    <p class="mt-1 whitespace-pre-wrap text-sm">${esc(order.Detalle || 'Sin observaciones')}</p>
                </section>
                <p class="mt-6 border-t border-slate-100 pt-3 text-center text-xs text-slate-400">Ticket de preparación · Pedido #${esc(order.id)}</p>
            </article>`;
            ticketDialog.classList.remove('hidden');
            ticketDialog.classList.add('flex');
        } catch (error) {
            notify(error.message || 'No se pudo consultar el pedido para generar el ticket.', true);
        }
    };
    const updateTotal = () => {
        let total = 0;
        items.querySelectorAll('[data-row]').forEach((row) => { const p = productMap.get(row.querySelector('select').value); const q = Number(row.querySelector('[data-quantity]').value) || 0; const price = p ? Number(p.Precio) : 0; const sub = price * q; total += sub; row.querySelector('[data-unit-price]').textContent = money(price); row.querySelector('[data-subtotal]').textContent = money(sub); });
        root.querySelector('[data-total]').textContent = money(total);
    };
    const syncStock = async (row) => {
        const select = row.querySelector('select'), quantity = row.querySelector('[data-quantity]'), stockLabel = row.querySelector('[data-stock]');
        const productId = select.value;
        row.dataset.stockRequest = String(Number(row.dataset.stockRequest || 0) + 1);
        const requestId = row.dataset.stockRequest;
        if (!productId) {
            row.dataset.stockProduct = '';
            quantity.disabled = true; quantity.removeAttribute('max'); quantity.value = 1;
            stockLabel.textContent = 'Selecciona un producto'; updateTotal(); return;
        }
        quantity.disabled = true; stockLabel.textContent = 'Consultando existencias…';
        try {
            const product = await api.get(`productos/${encodeURIComponent(productId)}`);
            if (row.dataset.stockRequest !== requestId) return;
            const stock = Math.max(0, Number.parseInt(product.Cantidad, 10) || 0);
            row.dataset.stockProduct = String(product.id);
            productMap.set(String(product.id), product);
            quantity.min = '1'; quantity.max = String(stock); quantity.disabled = stock < 1;
            if (stock < 1) {
                quantity.value = ''; stockLabel.textContent = 'Este producto no tiene unidades disponibles';
            } else {
                quantity.value = String(Math.min(Math.max(Number.parseInt(quantity.value, 10) || 1, 1), stock));
                stockLabel.textContent = `${stock} ${stock === 1 ? 'unidad disponible' : 'unidades disponibles'}`;
            }
            updateTotal();
        } catch (error) {
            if (row.dataset.stockRequest !== requestId) return;
            row.dataset.stockProduct = '';
            quantity.disabled = true; quantity.removeAttribute('max'); stockLabel.textContent = 'No se pudo consultar el stock';
        }
    };
    const addRow = (selected = '', quantity = 1) => {
        const row = document.createElement('div'); row.dataset.row = '';
        const opts = products.map((p) => `<option value="${esc(p.id)}" ${String(p.id) === String(selected) ? 'selected' : ''}>${esc(p.Nombre)} · ${money(p.Precio)}</option>`).join('');
        row.className = 'grid grid-cols-[minmax(8rem,1fr)_5.5rem_6rem_7rem_auto] items-center gap-2 rounded-md border border-pink-100 p-2';
        row.innerHTML = `<select aria-label="Producto" class="min-w-0 rounded border px-2 py-2"><option value="">Selecciona producto</option>${opts}</select><div><input aria-label="Cantidad" data-quantity type="number" min="1" step="1" value="${esc(quantity)}" disabled class="w-full rounded border px-2 py-2"><small data-stock class="block text-xs text-slate-600">Selecciona un producto</small></div><span data-unit-price class="text-right text-sm text-slate-600">0.00 Bs</span><span data-subtotal class="text-right text-sm font-medium">0.00 Bs</span><button type="button" data-remove class="rounded bg-pink-100 px-2 py-1 text-sm font-semibold text-pink-900 hover:bg-pink-200">Quitar</button>`;
        items.append(row); row.querySelector('select').addEventListener('change', () => syncStock(row));
        row.querySelector('[data-quantity]').addEventListener('input', (event) => {
            const input = event.currentTarget, max = Number(input.max);
            if (Number.isFinite(max) && max > 0 && Number(input.value) > max) input.value = String(max);
            updateTotal();
        });
        row.querySelector('[data-quantity]').addEventListener('change', (event) => {
            const input = event.currentTarget, max = Number(input.max);
            if (input.value && Number(input.value) < 1) input.value = '1';
            if (Number.isFinite(max) && max > 0 && Number(input.value) > max) input.value = String(max);
            updateTotal();
        });
        row.querySelector('[data-remove]').addEventListener('click', () => { row.remove(); updateTotal(); });
        if (selected) syncStock(row); updateTotal();
    };
    const reload = async () => {
        const records = await api.get('pedido');
        const rows = records.map((r, index) => {
            const lines = r.productos?.length ? r.productos : [];
            const action = `<button data-ticket="${esc(r.id)}" class="rounded bg-pink-100 px-3 py-1 font-semibold text-pink-900">Generar ticket</button> <button data-edit="${esc(r.id)}" class="rounded bg-lime-200 px-3 py-1 font-semibold text-lime-950">Modificar</button> <button data-delete="${esc(r.id)}" class="rounded bg-pink-100 px-3 py-1 font-semibold text-pink-900">Eliminar</button>`;
            return `<tr class="border-t border-pink-100 align-top"><td class="p-3">${index + 1}</td><td class="p-3">${esc(r.cliente || '—')}</td><td class="p-3">${lines.length ? lines.map((p) => esc(p.Nombre || 'Producto eliminado')).join('<br>') : 'Sin productos asociados'}</td><td class="p-3">${lines.length ? lines.map((p) => esc(p.Cantidad)).join('<br>') : '—'}</td><td class="p-3 font-semibold">${money(r.Total)}</td><td class="p-3">${esc(r.Metodo_de_pago || '—')}</td><td class="p-3">${esc(r.Estado)}</td><td class="p-3">${esc(r.Fecha_entrega || '—')}</td><td class="p-3 whitespace-nowrap">${action}</td></tr>`;
        }).join('');
        root.querySelector('[data-list]').innerHTML = `<div class="overflow-x-auto rounded-lg border border-pink-100"><table class="w-full min-w-[74rem] text-left text-sm"><thead class="bg-pink-100 text-pink-950"><tr><th class="p-3">N.º</th><th class="p-3">Cliente</th><th class="p-3">Producto</th><th class="p-3">Cantidad</th><th class="p-3">Total</th><th class="p-3">Método de pago</th><th class="p-3">Estado</th><th class="p-3">Fecha</th><th class="p-3">Acciones</th></tr></thead><tbody>${rows || '<tr><td class="p-4 text-center" colspan="9">No hay pedidos.</td></tr>'}</tbody></table></div>`;
        root.querySelectorAll('[data-edit]').forEach((b) => b.addEventListener('click', () => open(records.find((r) => String(r.id) === b.dataset.edit))));
        root.querySelectorAll('[data-ticket]').forEach((b) => b.addEventListener('click', () => showTicket(b.dataset.ticket)));
        root.querySelectorAll('[data-delete]').forEach((b) => b.addEventListener('click', async () => { if (!confirm(`¿Eliminar el pedido #${b.dataset.delete}?`)) return; try { const result = await api.delete(`pedido/${b.dataset.delete}`); if (!result.estado) throw new Error(Object.values(result.errores || {}).join('. ') || result.error); await reload(); notify(result.message); } catch (e) { notify(e.message, true); } }));
    };
    const open = (record = null) => { if (!catalogsReady) { notify('Cargando productos, clientes y personal. Inténtalo de nuevo en un momento.', true); return; } form.reset(); items.innerHTML = ''; form.elements.id.value = record?.id || ''; form.elements.Estado.value = record?.Estado || 'Pendiente'; form.elements.Metodo_de_pago.value = record?.Metodo_de_pago || ''; root.querySelector('[data-qr-help]').classList.toggle('hidden', form.elements.Metodo_de_pago.value !== 'QR'); form.elements.id_cliente.value = record?.id_cliente || ''; form.elements.id_personal.value = record?.id_personal || ''; form.elements.Detalle.value = record?.Detalle || ''; form.elements.Fecha_entrega.value = record?.Fecha_entrega ? String(record.Fecha_entrega).replace(' ', 'T').slice(0, 16) : ''; (record?.productos?.length ? record.productos : [{}]).forEach((p) => addRow(p.id_producto || '', p.Cantidad || 1)); root.querySelector('[data-title]').textContent = record ? `Modificar pedido #${record.id}` : 'Adicionar pedido'; modal.classList.remove('hidden'); modal.classList.add('flex'); };
    root.querySelector('[data-create]').addEventListener('click', () => open());
    root.querySelector('[data-add]').addEventListener('click', () => { if (!catalogsReady) { notify('Cargando productos. Inténtalo de nuevo en un momento.', true); return; } addRow(); });
    form.elements.Metodo_de_pago.addEventListener('change', (event) => {
        root.querySelector('[data-qr-help]').classList.toggle('hidden', event.currentTarget.value !== 'QR');
    });
    root.querySelectorAll('[data-close]').forEach((b) => b.addEventListener('click', () => { modal.classList.add('hidden'); modal.classList.remove('flex'); }));
    root.querySelector('[data-ticket-close]').addEventListener('click', () => { ticketDialog.classList.add('hidden'); ticketDialog.classList.remove('flex'); });
    ticketDialog.addEventListener('click', (event) => { if (event.target === ticketDialog) { ticketDialog.classList.add('hidden'); ticketDialog.classList.remove('flex'); } });
    root.querySelector('[data-ticket-print]').addEventListener('click', () => window.print());
    form.addEventListener('submit', async (event) => { event.preventDefault(); const rows = [...items.querySelectorAll('[data-row]')]; const productosSel = rows.map((r) => ({ id_producto: r.querySelector('select').value, Cantidad: Number(r.querySelector('[data-quantity]').value), stock: Number(r.querySelector('[data-quantity]').max), stockProduct: r.dataset.stockProduct }));
        if (!productosSel.length || productosSel.some((p) => !p.id_producto || p.stockProduct !== p.id_producto || !Number.isInteger(p.Cantidad) || p.Cantidad < 1 || !Number.isInteger(p.stock) || p.stock < 1 || p.Cantidad > p.stock)) { root.querySelector('[data-form-message]').textContent = 'Verifica cada producto: espera a que se consulte su stock y confirma una cantidad entre 1 y el máximo indicado.'; root.querySelector('[data-form-message]').className = 'text-sm text-red-700'; return; }
        const payload = { Estado: form.elements.Estado.value, Metodo_de_pago: form.elements.Metodo_de_pago.value, id_cliente: form.elements.id_cliente.value || null, id_personal: form.elements.id_personal.value || null, Detalle: form.elements.Detalle.value.trim() || null, Fecha_entrega: form.elements.Fecha_entrega.value ? `${form.elements.Fecha_entrega.value.replace('T', ' ')}:00` : null, productos: productosSel };
        const button = root.querySelector('[data-save]'); button.disabled = true;
        try { const id = form.elements.id.value; const result = id ? await api.put(`pedido/${id}`, payload) : await api.post('pedido', payload); if (!result.estado) throw new Error(Object.values(result.errores || {}).join('. ') || result.error || 'No se pudo guardar el pedido.'); const savedId = id || result.id; modal.classList.add('hidden'); modal.classList.remove('flex'); await reload(); notify(result.message); await showTicket(savedId); }
        catch (e) { const msg = root.querySelector('[data-form-message]'); msg.textContent = e.message; msg.className = 'text-sm text-red-700'; } finally { button.disabled = false; }
    });
    await reload();
    try {
        const [loadedProducts, clients, staff] = await Promise.all([
            api.get('productos'), api.get('clientes'), api.get('personal')
        ]);
        products = loadedProducts;
        productMap = new Map(products.map((product) => [String(product.id), product]));
        clients.forEach((client) => form.elements.id_cliente.add(new Option([client.Nombre, client.Apellidos].filter(Boolean).join(' '), client.id)));
        staff.forEach((person) => form.elements.id_personal.add(new Option(`${person.Nombre} · ${person.Cargo}`, person.id)));
        root.querySelector('[data-empty]').classList.toggle('hidden', products.length > 0);
        catalogsReady = true;
    } catch (error) {
        console.error('No se pudieron cargar los catálogos de Pedidos:', error);
        notify(`La lista de pedidos está disponible, pero no se pudieron cargar productos, clientes o personal: ${error.message || 'error de conexión'}`, true);
    }
}
