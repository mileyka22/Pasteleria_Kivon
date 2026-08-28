import { getUserList } from "./components/UserList.js";

const app = document.getElementById('app');

const loadView = async (path) => {
    const res = await fetch(path);
    app.innerHTML = await res.text();
};

const views = {
    home: async () => {
        await loadView('./src/views/home.html');
    },
    users: async () => {
        await loadView('./src/views/user.html');
        await getUserList();
    },
    productos: async () => {
        await loadView('./src/views/productos.html');
    },
    clientes: async () => {
        await loadView('./src/views/clientes.html');
    },
    pedidos: async () => {
        await loadView('./src/views/pedidos.html');
    },
    ventas: async () => {
        await loadView('./src/views/ventas.html');
    },
};

document.querySelectorAll('[data-view]').forEach(link => {
    link.addEventListener('click', async (event) => {
        event.preventDefault();
        const view = link.dataset.view;
        if (views[view]) {
            await views[view]();
        }
    });
});

views.home();
