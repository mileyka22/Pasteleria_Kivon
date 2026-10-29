import { rolePageUrl } from './roleNavigation.js';
import { API_BASE_URL } from '../utils/api.js';

const form = document.getElementById('login-form');
const message = document.getElementById('login-message');
const submit = document.getElementById('login-submit');
const redirectForUser = (user) => {
    const destination = rolePageUrl(user?.Permiso);
    if (destination) window.location.replace(destination);
    return Boolean(destination);
};

const getLoginUrl = (endpoint) => {
    if (API_BASE_URL.includes('api-proxy.php')) {
        const ep = String(endpoint).replace(/^\//, '');
        return 'http://www.pasteleria.com/api-proxy.php?endpoint=' + ep;
    }
    try {
        return new URL(endpoint, API_BASE_URL).href;
    } catch {
        return API_BASE_URL + String(endpoint).replace(/^\//, '');
    }
};

fetch(getLoginUrl('auth/me'), { credentials: 'include' })
    .then((response) => response.ok ? response.json() : null)
    .then((result) => {
        if (result?.user) redirectForUser(result.user);
    })
    .catch(() => {});

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const username = form.elements.Username.value.trim();
    const password = form.elements.Password.value;

    if (!username || !password) {
        message.textContent = 'Ingresa tu usuario and contrase��a.';
        message.className = 'rounded-xl bg-pink-50 px-4 py-3 text-sm text-pink-800';
        return;
    }

    submit.disabled = true;
    submit.querySelector('span').textContent = 'Verificando�?�';
    message.classList.add('hidden');

    try {
        const response = await fetch(getLoginUrl('auth/login'), {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ Username: username, Password: password }),
        });
        // console.log("REsponse=",response);
        const result = await response.json();
        // console.log("resultado=",result);
        if (!response.ok || !result.estado) throw new Error(result.error || 'No se pudo iniciar sesión.');

        const destination = rolePageUrl(result.user?.Permiso);
        if (!destination) {
            throw new Error('Tu cuenta inició sesión, pero su rol no tiene una página asignada.');
        }

        message.textContent = 'Acceso correcto. Abriendo el sistema…';
        message.className = 'rounded-xl bg-lime-50 px-4 py-3 text-sm text-lime-800';
        window.setTimeout(() => { window.location.replace(destination); }, 700);
    } catch (error) {
        message.textContent = error.message || 'No se pudo conectar con el servidor.';
        message.className = 'rounded-xl bg-pink-50 px-4 py-3 text-sm text-pink-800';
        submit.disabled = false;
        submit.querySelector('span').textContent = 'Ingresar al sistema';
    }
});
