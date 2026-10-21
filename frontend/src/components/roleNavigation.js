const rolePages = Object.freeze({
    administrador: '/index.html',
    ventas: '/indexVentas.html',
    produccion: '/indexProduccion.html',
});

export const normalizeRole = (role) => String(role ?? '')
    .trim()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase();

export const rolePageUrl = (role) => {
    const path = rolePages[normalizeRole(role)];
    return path ? new URL(path, window.location.origin).href : null;
};
