import { api } from '../utils/api.js';

const usersById = new Map();

const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
}[character]));

export const getUserList = async () => {
    const container = document.getElementById('userTableList');
    container.innerHTML = '<tr><td class="px-4 py-3 text-slate-500" colspan="4">Cargando usuarios...</td></tr>';

    try {
        const users = await api.get('usuario');
        usersById.clear();
        users.forEach((user) => usersById.set(String(user.id), user));

        container.innerHTML = users.map((user, index) => `
            <tr class="border-t border-pink-100">
                <td class="px-4 py-3">${index + 1}</td>
                <td class="px-4 py-3 font-medium text-slate-800">${escapeHtml(user.Username)}</td>
                <td class="px-4 py-3 text-slate-600">${escapeHtml(user.Permiso)}</td>
                <td class="px-4 py-3">
                    <div class="flex justify-end gap-2 whitespace-nowrap">
                        <button type="button" data-user-action="edit" data-user-id="${escapeHtml(user.id)}" class="rounded-md bg-lime-200 px-3 py-1.5 text-sm font-semibold text-lime-950 transition hover:bg-lime-300">Modificar</button>
                        <button type="button" data-user-action="delete" data-user-id="${escapeHtml(user.id)}" class="rounded-md bg-pink-100 px-3 py-1.5 text-sm font-semibold text-pink-900 transition hover:bg-pink-200">Eliminar</button>
                    </div>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Error al cargar usuarios:', error);
        container.innerHTML = '<tr><td class="px-4 py-3 text-pink-700" colspan="4">Error al cargar los usuarios</td></tr>';
    }
};

export const setupUserForm = () => {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');
    const message = document.getElementById('userFormMessage');
    const saveButton = document.getElementById('saveUser');
    const idGroup = document.getElementById('userIdGroup');
    const passwordGroup = document.getElementById('userPasswordGroup');
    const passwordInput = form.elements.Password;
    const deleteModal = document.getElementById('deleteUserModal');
    const deleteName = document.getElementById('deleteUserName');
    const deleteId = document.getElementById('deleteUserId');
    const deleteMessage = document.getElementById('deleteUserMessage');
    const confirmDeleteButton = document.getElementById('confirmDeleteUser');
    let userToDelete = null;

    const show = (element) => {
        element.classList.remove('hidden');
        element.classList.add('flex');
    };
    const hide = (element) => {
        element.classList.add('hidden');
        element.classList.remove('flex');
    };
    const closeForm = () => {
        hide(modal);
        form.reset();
        modal.dataset.mode = 'create';
        document.getElementById('userModalTitle').textContent = 'Adicionar usuario';
        saveButton.textContent = 'Guardar usuario';
        idGroup.classList.add('hidden');
        passwordGroup.classList.remove('hidden');
        passwordInput.disabled = false;
        passwordInput.required = true;
        message.classList.add('hidden');
    };
    const openCreateForm = () => {
        closeForm();
        show(modal);
        form.elements.Username.focus();
    };
    const openEditForm = (user) => {
        closeForm();
        modal.dataset.mode = 'edit';
        document.getElementById('userModalTitle').textContent = 'Modificar usuario';
        saveButton.textContent = 'Guardar cambios';
        idGroup.classList.remove('hidden');
        idGroup.classList.add('block');
        form.elements.id.value = user.id;
        form.elements.Username.value = user.Username ?? '';
        const permission = form.elements.Permiso;
        if (![...permission.options].some((option) => option.value === user.Permiso)) {
            permission.add(new Option(user.Permiso, user.Permiso));
        }
        permission.value = user.Permiso ?? '';
        passwordGroup.classList.add('hidden');
        passwordInput.disabled = true;
        passwordInput.required = false;
        show(modal);
        form.elements.Username.focus();
    };
    const closeDeleteForm = () => {
        hide(deleteModal);
        deleteMessage.classList.add('hidden');
        userToDelete = null;
    };

    document.getElementById('openUserModal').addEventListener('click', openCreateForm);
    document.getElementById('closeUserModal').addEventListener('click', closeForm);
    document.getElementById('cancelUserModal').addEventListener('click', closeForm);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeForm();
    });

    document.getElementById('userTableList').addEventListener('click', (event) => {
        const button = event.target.closest('button[data-user-action]');
        if (!button) return;
        const user = usersById.get(button.dataset.userId);
        if (!user) return;

        if (button.dataset.userAction === 'edit') {
            openEditForm(user);
            return;
        }

        userToDelete = user;
        deleteName.textContent = user.Username;
        deleteId.textContent = user.id;
        deleteMessage.classList.add('hidden');
        show(deleteModal);
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const editing = modal.dataset.mode === 'edit';
        const data = {
            Username: form.elements.Username.value.trim(),
            Permiso: form.elements.Permiso.value.trim(),
        };
        if (!editing) data.Password = passwordInput.value;

        saveButton.disabled = true;
        message.className = 'text-sm text-slate-600';
        message.textContent = editing ? 'Guardando cambios...' : 'Guardando usuario...';
        message.classList.remove('hidden');

        try {
            const result = editing
                ? await api.put(`usuario/${encodeURIComponent(form.elements.id.value)}`, data)
                : await api.post('usuario', data);
            if (!result.estado) {
                throw new Error(Object.values(result.errores ?? {}).join(', ') || result.error || 'No se pudo guardar el usuario.');
            }
            closeForm();
            await getUserList();
        } catch (error) {
            message.className = 'text-sm text-red-700';
            message.textContent = error.message || 'Error al guardar usuario.';
        } finally {
            saveButton.disabled = false;
        }
    });

    document.getElementById('cancelDeleteUser').addEventListener('click', closeDeleteForm);
    deleteModal.addEventListener('click', (event) => {
        if (event.target === deleteModal) closeDeleteForm();
    });
    confirmDeleteButton.addEventListener('click', async () => {
        if (!userToDelete) return;
        confirmDeleteButton.disabled = true;
        deleteMessage.className = 'mt-3 text-sm text-slate-600';
        deleteMessage.textContent = 'Eliminando usuario...';
        deleteMessage.classList.remove('hidden');

        try {
            const result = await api.delete(`usuario/${encodeURIComponent(userToDelete.id)}`);
            if (!result.estado) throw new Error(result.error || 'No se pudo eliminar el usuario.');
            closeDeleteForm();
            await getUserList();
        } catch (error) {
            deleteMessage.className = 'mt-3 text-sm text-red-700';
            deleteMessage.textContent = error.message || 'Error al eliminar usuario.';
        } finally {
            confirmDeleteButton.disabled = false;
        }
    });
};
