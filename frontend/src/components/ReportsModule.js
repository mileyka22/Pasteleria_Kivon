import { api } from '../utils/api.js';

const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
export async function setupReportsModule() {
    const root = document.getElementById('reportsModule');
    root.innerHTML = `<section class="rounded-lg border border-pink-200 bg-white p-5 shadow-sm"><h2 class="mb-4 text-2xl font-bold text-pink-900">Reportes de ventas</h2><form data-filter class="mb-5 flex flex-wrap items-end gap-3"><label class="text-sm font-medium">Fecha de inicio<input required name="inicio" type="date" class="mt-1 block rounded-md border border-slate-300 px-3 py-2"></label><label class="text-sm font-medium">Fecha final<input required name="fin" type="date" class="mt-1 block rounded-md border border-slate-300 px-3 py-2"></label><button class="rounded-md bg-pink-700 px-4 py-2 font-semibold text-white hover:bg-pink-800">Generar</button><button type="button" data-print class="hidden rounded-md bg-lime-700 px-4 py-2 font-semibold text-white">Imprimir reporte</button></form><p data-message class="mb-3 hidden rounded-md p-3 text-sm" role="status"></p><div data-report></div></section><style>@media print{body *{visibility:hidden!important}#reportsModule,#reportsModule *{visibility:visible!important}#reportsModule{position:absolute;inset:0;width:100%;padding:0!important;border:0!important;box-shadow:none!important}[data-filter],[data-message],[data-print]{display:none!important}}</style>`;
    const form = root.querySelector('[data-filter]'), output = root.querySelector('[data-report]'), message = root.querySelector('[data-message]'), print = root.querySelector('[data-print]');
    form.addEventListener('submit', async (event) => {
        event.preventDefault(); const start = form.elements.inicio.value, end = form.elements.fin.value;
        if (!start || !end || start > end) { message.textContent = 'Selecciona ambas fechas y verifica que el inicio no sea posterior al final.'; message.className = 'mb-3 rounded-md bg-pink-100 p-3 text-sm text-pink-900'; return; }
        try {
            const data = await api.get(`venta/reporte?inicio=${encodeURIComponent(start)}&fin=${encodeURIComponent(end)}`);
            message.className = 'hidden'; print.classList.toggle('hidden', !data.cantidad);
            if (!data.cantidad) { output.innerHTML = '<p class="rounded-md bg-lime-50 p-4">No se encontraron ventas en el periodo seleccionado.</p>'; return; }
            const rows = data.ventas.map(v => `<tr class="border-t"><td>${esc(v.Fecha)}</td><td>${esc(v.id)}</td><td>${esc(v.cliente || '—')}</td><td>${esc(v.personal || '—')}</td><td>${esc(v.productos || '—')}</td><td>${esc(v.Metodo_de_pago)}</td><td class="text-right">${Number(v.Total).toFixed(2)} Bs</td></tr>`).join('');
            output.innerHTML = `<article class="text-slate-800"><header class="mb-4 text-center"><p class="font-bold text-pink-900">PASTELERÍA KIVON</p><h1 class="text-xl font-bold">REPORTE DE VENTAS</h1><p>Periodo: ${esc(start)} al ${esc(end)}</p></header><table class="w-full border-collapse text-left text-sm"><thead><tr><th>Fecha</th><th>N.º</th><th>Cliente</th><th>Personal</th><th>Productos (cantidad y precio unitario)</th><th>Pago</th><th class="text-right">Total</th></tr></thead><tbody>${rows}</tbody><tfoot><tr><th colspan="6" class="pt-3 text-right">${data.cantidad} ventas · Total general</th><th class="pt-3 text-right">${Number(data.total).toFixed(2)} Bs</th></tr></tfoot></table><p class="mt-5 text-right text-xs">Generado: ${new Date().toLocaleString('es-BO')}</p></article>`;
        } catch (error) { message.textContent = error.message; message.className = 'mb-3 rounded-md bg-pink-100 p-3 text-sm text-pink-900'; }
    });
    print.addEventListener('click', () => window.print());
}
