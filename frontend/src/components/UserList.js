import { api } from "../utils/api.js";

export const getUserList = async () => {
    const container = document.getElementById('userTableList');
    container.innerHTML = '<tr><td class="px-4 py-3 text-slate-500" colspan="3">Cargando usuarios...</td></tr>';
    try {
        const users = await api.get('usuario');
        container.innerHTML = users.map(user =>`
            <tr class="border-t border-pink-100">
                <td class="px-4 py-3">${user.id}</td>
                <td class="px-4 py-3 font-medium text-slate-800">${user.name ?? user.nombre ?? 'Sin nombre'}</td>
                <td class="px-4 py-3 text-slate-600">${user.email ?? user.correo ?? 'Sin correo'}</td>
            </tr>
        `).join('');
        
    } catch (error) {
        container.innerHTML = '<tr><td class="px-4 py-3 text-pink-700" colspan="3">Error al cargar los usuarios</td></tr>';
    }
};
