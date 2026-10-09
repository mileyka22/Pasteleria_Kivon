const fullName = (person) => [person.Nombre, person.Apellidos].filter(Boolean).join(' ');

const personalField = {
    name: 'id_personal', label: 'Responsable', type: 'select', endpoint: 'personal', optionLabel: (item) => `${item.Nombre} · ${item.Cargo}`
};
const clientField = {
    name: 'id_cliente', label: 'Cliente', type: 'select', endpoint: 'clientes', optionLabel: fullName
};
const staffField = {
    name: 'id_personal', label: 'Personal', type: 'select', endpoint: 'personal', optionLabel: (item) => `${item.Nombre} · ${item.Cargo}`
};

export const crudModules = {
    productos: {
        rootId: 'productosModule', endpoint: 'productos', singular: 'producto', title: 'Productos', primaryField: 'Nombre', layout: 'cards',
        image: './src/assets/productos-pasteles.png',
        fields: [
            { name: 'Nombre', label: 'Nombre', required: true, maxLength: 80 },
            { name: 'Precio', label: 'Precio (Bs)', type: 'number', required: true, min: 0, step: '0.01' },
            { name: 'Cantidad', label: 'Cantidad disponible', type: 'number', required: true, min: 0, step: '1' },
            { name: 'Ingrediente', label: 'Ingredientes', type: 'textarea', maxLength: 255 },
            personalField
        ],
        cardFields: [
            { name: 'Precio', label: 'Precio', format: 'currency' },
            { name: 'Cantidad', label: 'Disponibles' },
            { name: 'Ingrediente', label: 'Ingredientes' },
            personalField
        ]
    },
    clientes: {
        rootId: 'clientesModule', endpoint: 'clientes', singular: 'cliente', title: 'Clientes', primaryField: 'Nombre',
        columns: [
            { name: 'Nombre', label: 'Nombre' }, { name: 'Apellidos', label: 'Apellidos' }, { name: 'CI', label: 'CI' },
            { name: 'Telefono', label: 'Telefono' }, { name: 'Direccion', label: 'Direccion' }
        ],
        fields: [
            { name: 'Nombre', label: 'Nombre', required: true, maxLength: 50 },
            { name: 'Apellidos', label: 'Apellidos', maxLength: 100 },
            { name: 'CI', label: 'CI', required: true, maxLength: 30 },
            { name: 'Telefono', label: 'Telefono', maxLength: 20 },
            { name: 'Direccion', label: 'Direccion', maxLength: 250 }
        ]
    },
    personal: {
        rootId: 'personalModule', endpoint: 'personal', singular: 'registro de personal', title: 'Personal', primaryField: 'Nombre',
        columns: [
            { name: 'Nombre', label: 'Nombre' }, { name: 'Cargo', label: 'Cargo' }, { name: 'Telefono', label: 'Telefono' },
            { name: 'Salario', label: 'Salario (Bs)', format: 'currency' },
            { name: 'id_usuario', label: 'Usuario', endpoint: 'usuario', optionLabel: (item) => item.Username }
        ],
        fields: [
            { name: 'Nombre', label: 'Nombre', required: true, maxLength: 50 },
            { name: 'Cargo', label: 'Cargo', required: true, maxLength: 100 },
            { name: 'Telefono', label: 'Telefono', maxLength: 50 },
            { name: 'Salario', label: 'Salario (Bs)', type: 'number', required: true, min: 0, step: '0.01' },
            { name: 'id_usuario', label: 'Usuario vinculado', type: 'select', endpoint: 'usuario', optionLabel: (item) => item.Username }
        ]
    },
    pedidos: {
        rootId: 'pedidosModule', endpoint: 'pedido', singular: 'pedido', title: 'Pedidos', primaryField: 'Estado',
        columns: [
            { name: 'Estado', label: 'Estado' }, { name: 'Detalle', label: 'Detalle' }, { name: 'Fecha_entrega', label: 'Entrega' },
            { ...clientField, label: 'Cliente' }, { ...staffField, label: 'Personal' }
        ],
        fields: [
            { name: 'Estado', label: 'Estado', type: 'select', required: true, options: [
                { value: 'Pendiente', label: 'Pendiente' }, { value: 'En proceso', label: 'En proceso' },
                { value: 'Entregado', label: 'Entregado' }, { value: 'Cancelado', label: 'Cancelado' }
            ] },
            { name: 'Detalle', label: 'Detalle', type: 'textarea', maxLength: 255 },
            { name: 'Fecha_entrega', label: 'Fecha de entrega', type: 'datetime-local', step: '1' },
            clientField,
            staffField
        ]
    },
    ventas: {
        rootId: 'ventasModule', endpoint: 'venta', singular: 'venta', title: 'Ventas', primaryField: 'Metodo_de_pago',
        columns: [
            { name: 'Fecha', label: 'Fecha' }, { name: 'Total', label: 'Total (Bs)', format: 'currency' },
            { name: 'Metodo_de_pago', label: 'Metodo de pago' }, { ...staffField, label: 'Personal' },
            { name: 'id_pedido', label: 'Pedido', endpoint: 'pedido', optionLabel: (item) => `Pedido ${item.id} · ${item.Estado}` }
        ],
        fields: [
            { name: 'Total', label: 'Total (Bs)', type: 'number', required: true, min: 0, step: '0.01' },
            { name: 'Metodo_de_pago', label: 'Metodo de pago', required: true, maxLength: 255 },
            staffField,
            { name: 'id_pedido', label: 'Pedido relacionado', type: 'select', endpoint: 'pedido', optionLabel: (item) => `Pedido ${item.id} · ${item.Estado}` }
        ]
    }
};
