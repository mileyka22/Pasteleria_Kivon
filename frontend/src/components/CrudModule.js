import { api, assetUrl } from '../utils/api.js';

const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
}[character]));

const asArray = (value) => Array.isArray(value) ? value : [];

export const setupCrudModule = async (rootId, config) => {
    const root = document.getElementById(rootId);
    const listId = `${rootId}List`;
    const modalId = `${rootId}Modal`;
    const deleteModalId = `${rootId}DeleteModal`;
    const title = escapeHtml(config.title);
    const columns = config.layout === 'cards' ? [] : config.columns;

    root.innerHTML = `
        <section class="rounded-lg border border-pink-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div><p class="text-sm font-semibold uppercase tracking-wide text-lime-700">Administracion</p><h2 class="mt-1 text-2xl font-bold text-pink-900">${title}</h2></div>
                <button type="button" data-open-create class="rounded-md bg-pink-700 px-4 py-2 font-semibold text-white transition hover:bg-pink-800">Adicionar ${escapeHtml(config.singular)}</button>
            </div>
            <p data-module-message class="mb-4 hidden rounded-md px-3 py-2 text-sm" role="status"></p>
            <div data-module-list id="${listId}"></div>
        </section>
        <div id="${modalId}" class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="${rootId}ModalTitle">
            <section class="my-auto w-full max-w-xl rounded-lg bg-white p-6 shadow-xl">
                <div class="mb-5 flex items-center justify-between"><h2 id="${rootId}ModalTitle" class="text-xl font-bold text-pink-900">${title}</h2><button type="button" data-close-form class="rounded px-2 py-1 text-2xl text-slate-500 hover:bg-slate-100" aria-label="Cerrar">&times;</button></div>
                <form data-record-form class="space-y-4">
                    <label data-id-group class="hidden block text-sm font-medium text-slate-700">ID del registro<input name="recordId" readonly class="mt-1 w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-slate-600"></label>
                    <div class="grid gap-4 sm:grid-cols-2">${config.fields.map(renderField).join('')}</div>
                    ${config.imageUpload ? `<div class="space-y-2"><span class="block text-sm font-medium text-slate-700">Fotografía del producto</span><div class="relative flex aspect-[4/3] max-h-64 items-center justify-center overflow-hidden rounded-md border border-dashed border-pink-300 bg-pink-50"><div data-image-placeholder class="text-center text-pink-800"><svg aria-hidden="true" class="mx-auto h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg><span class="mt-2 block text-sm">Agregar imagen</span></div><img data-image-preview alt="Vista previa del producto" class="hidden h-full w-full object-cover"></div><input data-image-input name="imagen" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="sr-only"><button type="button" data-image-select class="rounded-md border border-pink-300 bg-white px-3 py-2 text-sm font-semibold text-pink-900 hover:bg-pink-50">Seleccionar imagen</button><p data-image-message class="hidden text-sm" role="status"></p></div>` : ''}
                    <p data-form-message class="hidden text-sm" role="status"></p>
                    <div class="flex justify-end gap-2 pt-2"><button type="button" data-close-form class="rounded-md border border-slate-300 px-4 py-2 text-slate-700 hover:bg-slate-50">Cancelar</button><button type="submit" data-save-record class="rounded-md bg-lime-700 px-4 py-2 font-semibold text-white hover:bg-lime-800">Guardar</button></div>
                </form>
            </section>
        </div>
        <div id="${deleteModalId}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="${rootId}DeleteTitle">
            <section class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl"><h2 id="${rootId}DeleteTitle" class="text-xl font-bold text-pink-900">Eliminar ${escapeHtml(config.singular)}</h2><p data-delete-prompt class="mt-3 text-slate-700"></p><p data-delete-message class="mt-3 hidden text-sm" role="status"></p><div class="mt-6 flex justify-end gap-2"><button type="button" data-cancel-delete class="rounded-md border border-slate-300 px-4 py-2 text-slate-700 hover:bg-slate-50">Cancelar</button><button type="button" data-confirm-delete class="rounded-md bg-pink-700 px-4 py-2 font-semibold text-white hover:bg-pink-800">Eliminar</button></div></section>
        </div>`;

    const modal = document.getElementById(modalId);
    const deleteModal = document.getElementById(deleteModalId);
    const form = root.querySelector('[data-record-form]');
    const formMessage = root.querySelector('[data-form-message]');
    const imageInput = config.imageUpload ? root.querySelector('[data-image-input]') : null;
    const imagePreview = config.imageUpload ? root.querySelector('[data-image-preview]') : null;
    const imagePlaceholder = config.imageUpload ? root.querySelector('[data-image-placeholder]') : null;
    let previewUrl = null;
    const deleteMessage = root.querySelector('[data-delete-message]');
    const list = document.getElementById(listId);
    const message = root.querySelector('[data-module-message]');
    const recordMap = new Map();
    const optionMap = new Map();
    let selectedForDelete = null;

    const show = (element) => { element.classList.remove('hidden'); element.classList.add('flex'); };
    const hide = (element) => { element.classList.add('hidden'); element.classList.remove('flex'); };
    const setMessage = (text, kind = 'success') => {
        message.textContent = text;
        message.className = `mb-4 rounded-md px-3 py-2 text-sm ${kind === 'error' ? 'bg-pink-100 text-pink-900' : 'bg-lime-100 text-lime-900'}`;
    };
    const closeForm = () => { hide(modal); form.reset(); form.dataset.mode = 'create'; formMessage.classList.add('hidden'); if (previewUrl) URL.revokeObjectURL(previewUrl); previewUrl = null; };
    const closeDelete = () => { hide(deleteModal); deleteMessage.classList.add('hidden'); selectedForDelete = null; };

    const loadOptions = async () => {
    for (const field of config.fields) {
        if (field.endpoint) {
            try {
                const options = asArray(await api.get(field.endpoint));
                optionMap.set(field.name, new Map(options.map((item) => [String(item.id), item])));
                const select = form.elements[field.name];
                options.forEach((item) => select.add(new Option(field.optionLabel(item), item.id)));
            } catch (error) {
                console.error(`No se pudieron cargar opciones para ${field.name}:`, error);
                setMessage(`No se pudieron cargar las opciones de ${field.label}. Revisa la conexión y vuelve a abrir el módulo.`, 'error');
            }
        }
    }
    };
    if (!config.loadListBeforeOptions) await loadOptions();

    if (config.endpoint === 'venta') {
        const orderSelect = form.elements.id_pedido;
        const totalInput = form.elements.Total;
        totalInput.readOnly = true;
        orderSelect.addEventListener('change', () => {
            const order = optionMap.get('id_pedido')?.get(String(orderSelect.value));
            totalInput.value = order ? Number(order.Total || 0).toFixed(2) : '';
        });
    }

    const display = (field, record) => {
        const value = record[field.name];
        if (field.format === 'currency' && value !== null && value !== '') return `${Number(value).toFixed(2)} Bs`;
        if (field.endpoint && value !== null && value !== '') {
            const related = optionMap.get(field.name)?.get(String(value));
            return related ? field.optionLabel(related) : '—';
        }
        return value === null || value === undefined || value === '' ? '—' : String(value);
    };

    const actionButtons = (record) => `
        <div class="flex justify-end gap-2 whitespace-nowrap">
            <button type="button" data-record-action="edit" data-record-id="${escapeHtml(record.id)}" class="rounded-md bg-lime-200 px-3 py-1.5 text-sm font-semibold text-lime-950 transition hover:bg-lime-300">Modificar</button>
            <button type="button" data-record-action="delete" data-record-id="${escapeHtml(record.id)}" class="rounded-md bg-pink-100 px-3 py-1.5 text-sm font-semibold text-pink-900 transition hover:bg-pink-200">Eliminar</button>
        </div>`;

    const renderList = (records) => {
        recordMap.clear();
        records.forEach((record) => recordMap.set(String(record.id), record));
        if (config.layout === 'cards') {
            list.innerHTML = `<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">${records.map((record, index) => `
                <article class="flex min-w-0 flex-col overflow-hidden rounded-lg border border-pink-200 bg-white shadow-sm">
                    <div class="relative aspect-square bg-pink-50">${config.imageUpload && record.Imagen ? `<img src="${escapeHtml(assetUrl(record.Imagen))}" alt="Fotografía de ${escapeHtml(record.Nombre)}" class="h-full w-full object-cover">` : `<img src="${escapeHtml(config.image)}" alt="Imagen de reemplazo para ${escapeHtml(record.Nombre)}" class="h-full w-full object-cover">`}<span class="absolute left-3 top-3 rounded-full bg-white/90 px-3 py-1 text-sm font-semibold text-pink-900">${index + 1}</span></div>
                    <div class="flex flex-1 flex-col p-4"><h3 class="text-lg font-bold text-pink-900">${escapeHtml(record.Nombre)}</h3><dl class="mt-2 flex-1 space-y-1 text-sm text-slate-600">${config.cardFields.map((field) => `<div><dt class="inline font-semibold text-slate-700">${escapeHtml(field.label)}: </dt><dd class="inline break-words">${escapeHtml(display(field, record))}</dd></div>`).join('')}</dl><div class="mt-4">${actionButtons(record)}</div></div>
                </article>`).join('')}</div>`;
            return;
        }

        const headers = columns.map((column) => `<th class="px-3 py-3 font-semibold">${escapeHtml(column.label)}</th>`).join('');
        const rows = records.map((record, index) => `<tr class="border-t border-pink-100"><td class="px-3 py-3">${index + 1}</td>${columns.map((column) => `<td class="px-3 py-3">${escapeHtml(display(column, record))}</td>`).join('')}<td class="px-3 py-3">${actionButtons(record)}</td></tr>`).join('');
        list.innerHTML = `<div class="overflow-x-auto rounded-lg border border-pink-100"><table class="w-full min-w-[44rem] border-collapse text-left text-sm"><thead class="bg-pink-100 text-pink-950"><tr><th class="px-3 py-3 font-semibold">N.º</th>${headers}<th class="px-3 py-3 text-right font-semibold">Acciones</th></tr></thead><tbody class="divide-y divide-pink-100 bg-white">${rows || `<tr><td colspan="${columns.length + 2}" class="px-3 py-5 text-center text-slate-500">No hay registros para mostrar.</td></tr>`}</tbody></table></div>`;
    };

    const reload = async () => {
        list.innerHTML = '<p class="py-5 text-slate-500">Cargando registros...</p>';
        try {
            const records = asArray(await api.get(config.endpoint));
            renderList(records);
        } catch (error) {
            console.error(`Error al cargar ${config.title}:`, error);
            list.innerHTML = `<p class="rounded-md bg-pink-100 px-4 py-3 text-pink-900">${escapeHtml(error.message || 'No se pudieron cargar los registros.')}</p>`;
        }
    };

    const openForm = (mode, record = null) => {
        form.reset();
        if (config.production) {
            ['Nombre', 'Precio', 'Ingrediente'].forEach((name) => {
                const field = form.elements[name];
                if (field) { field.readOnly = mode === 'edit' && name === 'Nombre'; field.disabled = mode === 'edit' && name !== 'Nombre'; }
            });
            const imageSection = imageInput?.closest('div.space-y-2');
            imageSection?.classList.toggle('hidden', mode === 'edit');
        }
        if (imageInput) {
            imageInput.value = '';
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = null;
            const imageMessage = root.querySelector('[data-image-message]');
            imageMessage.classList.add('hidden');
            const currentImage = record?.Imagen ? assetUrl(record.Imagen) : '';
            imagePreview.src = currentImage;
            imagePreview.classList.toggle('hidden', !currentImage);
            imagePlaceholder.classList.toggle('hidden', Boolean(currentImage));
        }
        form.dataset.mode = mode;
        form.querySelector('[data-id-group]').classList.toggle('hidden', mode === 'create');
        form.elements.recordId.value = record?.id ?? '';
        root.querySelector(`#${rootId}ModalTitle`).textContent = mode === 'edit' ? `Modificar ${config.singular}` : `Adicionar ${config.singular}`;
        root.querySelector('[data-save-record]').textContent = mode === 'edit' ? 'Guardar cambios' : 'Guardar';
        if (record) {
            for (const field of config.fields) {
                let value = record[field.name] ?? '';
                if (field.type === 'datetime-local' && value) value = String(value).replace(' ', 'T').slice(0, 19);
                form.elements[field.name].value = value;
            }
        }
        formMessage.classList.add('hidden');
        hide(deleteModal);
        show(modal);
        form.elements[config.fields[0].name].focus();
    };

    if (imageInput) {
        root.querySelector('[data-image-select]').addEventListener('click', () => imageInput.click());
        imageInput.addEventListener('change', () => {
            const file = imageInput.files?.[0];
            const imageMessage = root.querySelector('[data-image-message]');
            if (!file) return;
            if (file.size > 5 * 1024 * 1024) {
                imageInput.value = '';
                imageMessage.textContent = 'La imagen no debe superar 5 MB.';
                imageMessage.className = 'text-sm text-red-700';
                return;
            }
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = URL.createObjectURL(file);
            imagePreview.src = previewUrl;
            imagePreview.classList.remove('hidden');
            imagePlaceholder.classList.add('hidden');
            imageMessage.textContent = 'Imagen seleccionada; se guardará al guardar el producto.';
            imageMessage.className = 'text-sm text-lime-800';
        });
    }

    root.querySelector('[data-open-create]').addEventListener('click', () => openForm('create'));
    root.querySelectorAll('[data-close-form]').forEach((button) => button.addEventListener('click', closeForm));
    modal.addEventListener('click', (event) => { if (event.target === modal) closeForm(); });
    list.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-record-action]');
        if (!button) return;
        const record = recordMap.get(button.dataset.recordId);
        if (!record) return;
        if (button.dataset.recordAction === 'edit') {
            openForm('edit', record);
            return;
        }
        selectedForDelete = record;
        const label = record[config.primaryField] ?? config.singular;
        root.querySelector('[data-delete-prompt]').textContent = `¿Confirmas eliminar ${config.singular} «${label}» (ID ${record.id})?`;
        deleteMessage.classList.add('hidden');
        show(deleteModal);
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const editing = form.dataset.mode === 'edit';
        const values = {};
        for (const field of config.fields) {
            const input = form.elements[field.name];
            let value = input.value.trim();
            if (field.type === 'number' && value !== '') value = Number(value);
            if (field.type === 'datetime-local' && value) value = value.length === 16
                ? `${value.replace('T', ' ')}:00`
                : value.replace('T', ' ');
            values[field.name] = value === '' ? null : value;
        }

        const save = root.querySelector('[data-save-record]');
        save.disabled = true;
        formMessage.className = 'text-sm text-slate-600';
        formMessage.textContent = editing ? 'Guardando cambios...' : 'Guardando registro...';
        formMessage.classList.remove('hidden');
        try {
            let result;
            if (config.imageUpload) {
                const formData = new FormData();
                Object.entries(values).forEach(([key, value]) => formData.append(key, value ?? ''));
                if (imageInput.files?.[0]) formData.append('imagen', imageInput.files[0]);
                result = editing
                    ? await api.postForm(`${config.endpoint}/${encodeURIComponent(form.elements.recordId.value)}`, formData)
                    : await api.postForm(config.endpoint, formData);
            } else {
                result = editing
                    ? await api.put(`${config.endpoint}/${encodeURIComponent(form.elements.recordId.value)}`, values)
                    : await api.post(config.endpoint, values);
            }
            if (!result.estado) throw new Error(result.error || Object.values(result.errores ?? {}).join('. ') || 'La operación no se completó.');
            closeForm();
            setMessage(result.message || `${config.singular} guardado correctamente.`);
            await reload();
        } catch (error) {
            formMessage.className = 'text-sm text-red-700';
            formMessage.textContent = error.message || 'No se pudo guardar el registro.';
        } finally {
            save.disabled = false;
        }
    });

    root.querySelector('[data-cancel-delete]').addEventListener('click', closeDelete);
    deleteModal.addEventListener('click', (event) => { if (event.target === deleteModal) closeDelete(); });
    root.querySelector('[data-confirm-delete]').addEventListener('click', async (event) => {
        if (!selectedForDelete) return;
        const button = event.currentTarget;
        button.disabled = true;
        deleteMessage.className = 'mt-3 text-sm text-slate-600';
        deleteMessage.textContent = 'Eliminando registro...';
        deleteMessage.classList.remove('hidden');
        try {
            const result = await api.delete(`${config.endpoint}/${encodeURIComponent(selectedForDelete.id)}`);
            if (!result.estado) throw new Error(result.error || Object.values(result.errores ?? {}).join('. ') || 'La operación no se completó.');
            closeDelete();
            setMessage(result.message || `${config.singular} eliminado correctamente.`);
            await reload();
        } catch (error) {
            deleteMessage.className = 'mt-3 text-sm text-red-700';
            deleteMessage.textContent = error.message || 'No se pudo eliminar el registro.';
        } finally {
            button.disabled = false;
        }
    });

    await reload();
    if (config.loadListBeforeOptions) {
        await loadOptions();
        await reload();
    }
};

function renderField(field)
{
    const required = field.required ? 'required' : '';
    const maxLength = field.maxLength ? `maxlength="${field.maxLength}"` : '';
    const min = field.min !== undefined ? `min="${field.min}"` : '';
    const step = field.step ? `step="${field.step}"` : '';
    let input;
    if (field.type === 'textarea') {
        input = `<textarea name="${escapeHtml(field.name)}" ${required} ${maxLength} rows="3" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></textarea>`;
    } else if (field.type === 'select') {
        const placeholder = field.required ? 'Selecciona una opción' : 'Sin asignar';
        const options = (field.options || []).map((option) => `<option value="${escapeHtml(option.value)}">${escapeHtml(option.label)}</option>`).join('');
        input = `<select name="${escapeHtml(field.name)}" ${required} class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2"><option value="">${placeholder}</option>${options}</select>`;
    } else {
        input = `<input name="${escapeHtml(field.name)}" type="${escapeHtml(field.type || 'text')}" ${required} ${maxLength} ${min} ${step} class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">`;
    }
    return `<label class="block text-sm font-medium text-slate-700">${escapeHtml(field.label)}${input}</label>`;
}
