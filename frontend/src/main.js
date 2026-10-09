import { getUserList, setupUserForm } from "./components/UserList.js";
import { setupCrudModule } from "./components/CrudModule.js";
import { crudModules } from "./components/crudModules.js";

const app = document.getElementById('app');

const loadView = async (path) => {
    const res = await fetch(path, { cache: 'no-cache' });
    if (!res.ok) {
        throw new Error(`No se pudo cargar la vista (${res.status}): ${path}`);
    }
    app.innerHTML = await res.text();
};

const views = {
    home: async () => {
        await loadView('./src/views/home.html');
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
        await setupCrudModule(crudModules.pedidos.rootId, crudModules.pedidos);
    },
    ventas: async () => {
        await loadView('./src/views/ventas.html');
        await setupCrudModule(crudModules.ventas.rootId, crudModules.ventas);
    },
};

document.querySelectorAll('[data-view]').forEach(link => {
    link.addEventListener('click', async (event) => {
        event.preventDefault();
        const view = link.dataset.view;
        if (views[view]) {
            try {
                await views[view]();
            } catch (error) {
                console.error('Error al cargar la sección:', error);
                app.textContent = 'No se pudo cargar esta sección. Revisa la consola y la ruta del servidor.';
            }
        }
    });
});

views.home().catch((error) => {
    console.error('Error al cargar Inicio:', error);
    app.textContent = 'No se pudo cargar Inicio. Revisa la consola y la ruta del servidor.';
});
