// 1. Detectar el origen y dominio actual del navegador
const hostname = typeof window !== 'undefined' && window.location ? window.location.hostname : '';
const origin = typeof window !== 'undefined' && window.location ? window.location.origin : '';

// 2. DETECCIÓN INVERSA (Más segura): Si el dominio no incluye "render.com", es 100% tu entorno local
const isLocalEnv = !hostname.includes('onrender.com');

// 3. URLs del Backend por Entorno
const PROD_API = 'https://pasteleria-kivon.onrender.com/';
const LOCAL_API = `${origin}/api-proxy.php?endpoint=`;

export const API_BASE_URL = isLocalEnv ? LOCAL_API : PROD_API;
// 4. Configurar las imágenes de forma dinámica (Versión tradicional con signo +)
export const assetUrl = (path) => {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    
    // Quitamos la barra inicial si existe
    const cleanPath = path.replace(/^\//, '');
    
    if (isLocalEnv) {
        // Concatenación tradicional: añade la barra diagonal explícita antes de la variable
        return 'http://api.pasteleria/' + cleanPath;
    }
    
    return PROD_API + cleanPath;
};

// 5. Función de peticiones corregida eliminando el constructor conflictivo en local
const request = async (endpoint, options = {}) => {
    const cleanEndpoint = String(endpoint).replace(/^\//, '');
    let url;

    if (isLocalEnv) {
        // Obligamos a concatenar strings planos en local para mantener el api-proxy.php intacto
        url = `${API_BASE_URL}${cleanEndpoint}`;
    } else {
        // En producción (Render) mantiene tu enrutamiento original
        url = new URL(cleanEndpoint, API_BASE_URL).href;
    }

    const response = await fetch(url, { credentials: 'include', ...options });
    const body = await response.text();
    let data;

    try {
        data = JSON.parse(body);
    } catch {
        const preview = body.trim().slice(0, 180);
        throw new Error(`La API no devolvió JSON (HTTP ${response.status}). ${preview}`);
    }

    if (!response.ok) {
        if (response.status === 401 && !cleanEndpoint.startsWith('auth/')) {
            window.dispatchEvent(new Event('auth:expired'));
        }
        const validationErrors = Object.values(data.errores ?? {}).join('. ');
        throw new Error(data.message || data.error || validationErrors || `Error HTTP ${response.status}`);
    }
    return data;
};

export const api = {
    get: async (endpoint) => {
        try {
            return await request(endpoint);
        } catch (error) {
            console.error('Error al consultar la API:', error);
            throw error;
        }
    },
    post: (endpoint, data) => request(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    }),
    put: (endpoint, data) => request(endpoint, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    }),
    postForm: (endpoint, data) => request(endpoint, { method: 'POST', body: data }),
    delete: (endpoint) => request(endpoint, { method: 'DELETE' }),
};
