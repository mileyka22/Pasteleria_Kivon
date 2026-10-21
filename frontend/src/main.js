import { getUserList, setupUserForm } from "./components/UserList.js";
import { setupCrudModule } from "./components/CrudModule.js";
import { crudModules } from "./components/crudModules.js";
import { setupPedidosModule } from "./components/PedidosModule.js";
import { setupHomeDashboard } from "./components/HomeDashboard.js";
import { normalizeRole, rolePageUrl } from "./components/roleNavigation.js";
import { api } from "./utils/api.js";

const app = document.getElementById('app');
const sessionButton = document.querySelector('[data-session-button]');
const sessionUser = document.querySelector('[data-session-user]');
const loginUrl = new URL('./src/views/login.html', document.baseURI).href;
let currentUser = null;

const setSessionButton = (authenticated) => {
    sessionButton.textContent = authenticated ? 'Cerrar sesión' : 'Iniciar sesión';
    sessionButton.href = loginUrl;
    const username = authenticated && typeof currentUser?.Username === 'string' ? currentUser.Username : '';
    sessionUser.textContent = username ? `👤 ${username}` : '';
    sessionUser.classList.toggle('hidden', !username);
    sessionButton.setAttribute('aria-label', authenticated ? 'Cerrar sesión' : 'Iniciar sesión');
};

const showLoginPrompt = () => {
    app.innerHTML = `<section class="rounded-xl border border-pink-200 bg-white p-8 text-center shadow-sm"><p class="text-sm font-semibold uppercase tracking-wide text-lime-800">Pastelería Kivon</p><h2 class="mt-2 text-2xl font-bold text-pink-950">Inicia sesión para continuar</h2><p class="mt-2 text-slate-600">Inicia sesión para acceder a los módulos del sistema.</p><a href="${loginUrl}" class="mt-5 inline-flex rounded-md bg-pink-700 px-4 py-2 font-semibold text-white hover:bg-pink-800">Iniciar sesión</a></section>`;
};

const checkSession = async () => {
    try {
        const result = await api.get('auth/me');
        currentUser = result.user || null;
    } catch {
        currentUser = null;
    }
    if (currentUser) {
        const expectedRole = normalizeRole(document.body.dataset.rolePage);
        const userRole = normalizeRole(currentUser.Permiso);
        const userPage = rolePageUrl(userRole);
        if (expectedRole && userPage && expectedRole !== userRole) {
            window.location.replace(userPage);
            return;
        }
    }
    setSessionButton(Boolean(currentUser));
    if (currentUser) await views.home();
    else showLoginPrompt();
};

sessionButton.addEventListener('click', async (event) => {
    if (!currentUser) return;
    event.preventDefault();
    sessionButton.setAttribute('aria-disabled', 'true');
    try {
        await api.post('auth/logout', {});
        currentUser = null;
        setSessionButton(false);
        showLoginPrompt();
    } catch (error) {
        app.textContent = error.message || 'No se pudo cerrar la sesión.';
        if (error.message?.includes('401')) {
            currentUser = null;
            setSessionButton(false);
            showLoginPrompt();
        }
    } finally {
        sessionButton.removeAttribute('aria-disabled');
    }
});

window.addEventListener('auth:expired', () => {
    currentUser = null;
    setSessionButton(false);
    showLoginPrompt();
});

window.addEventListener('pageshow', (event) => {
    if (event.persisted) checkSession();
});

const loadView = async (path) => {
    const response = await fetch(path, { cache: 'no-cache' });
    if (!response.ok) throw new Error(`No se pudo cargar la vista (${response.status}): ${path}`);
    app.innerHTML = await response.text();
};

const views = {
    home: async () => {
        await loadView('./src/views/home.html');
        setupHomeDashboard();
    },
    users: async () => {
        await loadView('./src/views/user.html');
        setupUserForm();
        await getUserList();
    },
    productos: async () => {
        await loadView('./src/views/productos.html');
        await setupCrudModule(crudModules.productos.rootId, crudModules.productos);
    },
    clientes: async () => {
        await loadView('./src/views/clientes.html');
        await setupCrudModule(crudModules.clientes.rootId, crudModules.clientes);
    },
    personal: async () => {
        await loadView('./src/views/personal.html');
        await setupCrudModule(crudModules.personal.rootId, crudModules.personal);
    },
    pedidos: async () => {
        await loadView('./src/views/pedidos.html');
        await setupPedidosModule();
    },
    ventas: async () => {
        await loadView('./src/views/ventas.html');
        await setupCrudModule(crudModules.ventas.rootId, crudModules.ventas);
    }
};

document.querySelectorAll('[data-view]').forEach((link) => {
    link.addEventListener('click', async (event) => {
        event.preventDefault();
        if (!currentUser) {
            window.location.href = loginUrl;
            return;
        }
        const view = link.dataset.view;
        if (!views[view]) return;
        try {
            await views[view]();
        } catch (error) {
            console.error('Error al cargar la sección:', error);
            app.textContent = error.message || 'No se pudo cargar esta sección.';
        }
    });
});

checkSession().catch((error) => {
    console.error('No se pudo comprobar la sesión:', error);
    currentUser = null;
    setSessionButton(false);
    showLoginPrompt();
});
