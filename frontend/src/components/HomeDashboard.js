import { api } from '../utils/api.js';

const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
}[character]));

const money = (value) => `${new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(value) || 0)} Bs`;
const colors = ['#be185d', '#65a30d', '#db2777', '#84cc16', '#9d174d', '#4d7c0f'];
const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
let refreshTimer = null;

export function setupHomeDashboard() {
    const root = document.getElementById('app');
    const refreshButton = root?.querySelector('[data-dashboard-refresh]');
    if (!refreshButton) return;
    refreshButton.addEventListener('click', () => loadDashboard(root));
    loadDashboard(root);
    if (!refreshTimer) refreshTimer = setInterval(() => {
        const currentRoot = document.getElementById('app');
        if (currentRoot?.querySelector('[data-dashboard-refresh]')) loadDashboard(currentRoot);
    }, 60000);
}

async function loadDashboard(root) {
    const button = root.querySelector('[data-dashboard-refresh]');
    const status = root.querySelector('[data-dashboard-status]');
    if (!button || button.disabled) return;
    button.disabled = true;
    status.textContent = 'Actualizando estadísticas…';
    status.className = 'rounded-lg border border-lime-200 bg-lime-50 px-4 py-3 text-sm text-lime-900';
    try {
        const dashboard = await api.get('dashboard');
        renderDashboard(root, dashboard);
        status.className = 'hidden rounded-lg border px-4 py-3 text-sm';
        status.textContent = '';
    } catch (error) {
        status.textContent = `No se pudieron cargar las estadísticas: ${error.message || 'error de conexión'}`;
        status.className = 'rounded-lg border border-pink-200 bg-pink-50 px-4 py-3 text-sm text-pink-900';
    } finally {
        button.disabled = false;
    }
}

function renderDashboard(root, data) {
    const summary = data.resumen || {};
    root.querySelector('[data-stat-today]').textContent = money(summary.ventasHoy);
    root.querySelector('[data-stat-month]').textContent = money(summary.ventasMes);
    root.querySelector('[data-stat-year]').textContent = money(summary.ventasAnio);
    root.querySelector('[data-stat-count]').textContent = new Intl.NumberFormat('es-BO').format(Number(summary.cantidadVentasHoy) || 0);
    renderProductsChart(root.querySelector('[data-chart-products]'), data.productosMasVendidos || []);
    renderSalesChart(root.querySelector('[data-chart-sales]'), data.ventasPorMes || [], summary);
}

function renderProductsChart(container, products) {
    const valid = products
        .map((product) => ({ name: String(product.Nombre || 'Producto sin nombre'), quantity: Number(product.Cantidad) || 0 }))
        .filter((product) => product.quantity > 0);
    if (!valid.length) {
        container.innerHTML = emptyState('Aún no hay ventas asociadas a productos para mostrar.');
        return;
    }

    const visible = valid.slice(0, 5).map((product, index) => ({ ...product, color: colors[index] }));
    const otherQuantity = valid.slice(5).reduce((sum, product) => sum + product.quantity, 0);
    if (otherQuantity) visible.push({ name: 'Otros productos', quantity: otherQuantity, color: colors[5] });
    const total = visible.reduce((sum, product) => sum + product.quantity, 0);
    let angle = -Math.PI / 2;
    const slices = visible.map((product) => {
        const portion = product.quantity / total;
        const nextAngle = angle + portion * Math.PI * 2;
        let shape;
        if (visible.length === 1) {
            shape = `<circle cx="140" cy="140" r="112" fill="${product.color}"/>`;
        } else {
            const x1 = 140 + 112 * Math.cos(angle), y1 = 140 + 112 * Math.sin(angle);
            const x2 = 140 + 112 * Math.cos(nextAngle), y2 = 140 + 112 * Math.sin(nextAngle);
            const largeArc = portion > 0.5 ? 1 : 0;
            shape = `<path d="M 140 140 L ${x1.toFixed(3)} ${y1.toFixed(3)} A 112 112 0 ${largeArc} 1 ${x2.toFixed(3)} ${y2.toFixed(3)} Z" fill="${product.color}"><title>${escapeHtml(product.name)}: ${product.quantity} unidades</title></path>`;
        }
        angle = nextAngle;
        return shape;
    }).join('');

    container.innerHTML = `<div class="grid items-center gap-5 md:grid-cols-[minmax(180px,0.9fr)_1.1fr]"><svg class="mx-auto block h-auto w-full max-w-[280px]" viewBox="0 0 280 280" role="img" aria-label="Gráfico circular de productos más vendidos por cantidad"><title>Unidades vendidas por producto</title>${slices}</svg><ul class="space-y-3">${visible.map((product) => `<li class="flex min-w-0 items-start gap-3"><span class="mt-1.5 h-3 w-3 shrink-0 rounded-sm" style="background-color:${product.color}" aria-hidden="true"></span><span class="min-w-0 flex-1 break-words text-sm text-slate-700">${escapeHtml(product.name)}</span><span class="shrink-0 text-sm font-semibold text-slate-900">${product.quantity} u.</span></li>`).join('')}<li class="border-t border-slate-100 pt-3 text-sm font-semibold text-slate-700">Total: ${total} unidades</li></ul></div>`;
}

function renderSalesChart(container, salesByMonth, summary) {
    const year = Number(summary.anioActual) || new Date().getFullYear();
    const currentMonth = Math.min(12, Math.max(1, Number(summary.mesActual) || new Date().getMonth() + 1));
    const valuesByMonth = new Map(salesByMonth.map((item) => [Number(item.Mes), Number(item.Total) || 0]));
    const months = Array.from({ length: currentMonth }, (_, index) => ({
        name: monthNames[index], total: valuesByMonth.get(index + 1) || 0
    }));
    const total = months.reduce((sum, month) => sum + month.total, 0);
    const period = document.querySelector('[data-chart-period]');
    if (period) period.textContent = `Enero a ${monthNames[currentMonth - 1]} de ${year}`;
    if (!total) {
        container.innerHTML = emptyState(`Aún no hay ventas registradas en ${year}.`);
        return;
    }

    const width = 720, height = 310, left = 58, right = 16, top = 16, bottom = 52;
    const chartWidth = width - left - right, chartHeight = height - top - bottom, baseline = top + chartHeight;
    const maximum = Math.max(...months.map((month) => month.total));
    const step = chartWidth / months.length, barWidth = Math.min(34, step * 0.62);
    const grid = Array.from({ length: 5 }, (_, index) => {
        const ratio = index / 4, y = top + chartHeight * ratio, value = maximum * (1 - ratio);
        return `<line x1="${left}" y1="${y.toFixed(2)}" x2="${(width - right)}" y2="${y.toFixed(2)}" stroke="#e2e8f0" stroke-width="1"/><text x="${left - 8}" y="${(y + 4).toFixed(2)}" text-anchor="end" fill="#64748b" font-size="11">${escapeHtml(new Intl.NumberFormat('es-BO', { maximumFractionDigits: 0 }).format(value))}</text>`;
    }).join('');
    const bars = months.map((month, index) => {
        const barHeight = month.total > 0 ? Math.max(2, month.total / maximum * chartHeight) : 0;
        const x = left + index * step + (step - barWidth) / 2;
        const y = baseline - barHeight;
        const fill = index + 1 === currentMonth ? '#65a30d' : '#db2777';
        return `<g><rect x="${x.toFixed(2)}" y="${y.toFixed(2)}" width="${barWidth.toFixed(2)}" height="${barHeight.toFixed(2)}" rx="5" fill="${fill}"><title>${escapeHtml(month.name)}: ${escapeHtml(money(month.total))}</title></rect><text x="${(x + barWidth / 2).toFixed(2)}" y="${baseline + 24}" text-anchor="middle" fill="#475569" font-size="11">${escapeHtml(month.name.slice(0, 3))}</text></g>`;
    }).join('');
    container.innerHTML = `<div class="w-full overflow-x-auto"><svg class="h-auto min-w-[520px] w-full" viewBox="0 0 ${width} ${height}" role="img" aria-label="Ventas mensuales en bolivianos de enero a ${escapeHtml(monthNames[currentMonth - 1])} de ${year}"><title>Importe de ventas por mes</title>${grid}<line x1="${left}" y1="${baseline}" x2="${width - right}" y2="${baseline}" stroke="#94a3b8" stroke-width="1.2"/>${bars}</svg></div><p class="mt-2 text-center text-xs text-slate-500">Importes en Bs · datos de ventas registradas</p>`;
}

function emptyState(message) {
    return `<div class="flex min-h-64 items-center justify-center rounded-lg bg-slate-50 px-6 text-center text-sm text-slate-500">${escapeHtml(message)}</div>`;
}
