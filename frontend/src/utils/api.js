const API_URL = 'http://api.pasteleria/';

const request = async (endpoint, options = {}) => {
    const response = await fetch(`${API_URL}${endpoint}`, options);
    const body = await response.text();
    let data;

    try {
        data = JSON.parse(body);
    } catch {
        const preview = body.trim().slice(0, 180);
        throw new Error(`La API no devolvió JSON (HTTP ${response.status}). ${preview}`);
    }

    if (!response.ok) {
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
    delete: (endpoint) => request(endpoint, { method: 'DELETE' }),
};
