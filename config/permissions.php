<?php

return [
    'modules' => [

        'dashboard' => [
            'label'   => 'Dashboard',
            'actions' => [
                'view'   => ['Ver', 'Acceso a la vista principal del dashboard'],
                'export' => ['Exportar','Exportar KPIs'],
                'view_users_actives' => ['Ver usuarios activos','Ver usuarios activos'],
                'view_users_registered' => ['Ver usuarios registrados','Ver usuarios registrados'],
                'graph_type_users' => ['Ver grafico Tipo usuarios','Ver grafico Tipo usuarios'],
                'graph_sale' => ['Ver gráfico Ventas','Ver gráfico Ventas'],
                'graph_income_output' => ['Ver gráfico Ingresos y Salidas','Ver gráfico Ingresos y Salidas'],
                'graph_promotions_usage' => ['Ver gráfico Promociones usadas','Ver gráfico Promociones usadas'],
                'graph_customers_top' => ['Ver gráfico Clientes con más pedidos','Ver gráfico Clientes con más pedidos'],
            ],
        ],

        'accesos' => [
            'label'   => 'Accesos',
            'actions' => [
                'enable'       => ['Habilitar',   'Habilitar accesos'],
                'users'        => ['Usuarios',    'Habilitar usuarios'],
                'roles'        => ['Roles',   'Habilitar roles'],
                'permissions'  => ['Permisos', 'Habilitar permisos'],

            ],
        ],

        'usuarios' => [
            'label'   => 'Usuarios',
            'actions' => [
                'list'     => ['Listar',   'Listar usuarios'],
                'create'   => ['Crear',    'Crear usuario'],
                'edit'     => ['Editar',   'Editar usuario'],
                'destroy'  => ['Eliminar', 'Eliminar (soft delete) usuario'],
                'restore'  => ['Restaurar','Restaurar usuario'],
            ],
        ],

        'roles' => [
            'label'   => 'Roles',
            'actions' => [
                'list'     => ['Listar',   'Listar roles'],
                'create'   => ['Crear',    'Crear rol'],
                'edit'     => ['Editar',   'Editar rol'],
                'destroy'  => ['Eliminar', 'Eliminar rol'],
                'assign'   => ['Asignar',  'Asignar permisos a rol'],
                'search'   => ['Buscar',   'Buscar roles'],
            ],
        ],

        'permisos' => [
            'label'   => 'Permisos',
            'actions' => [
                'list'     => ['Listar',   'Listar permisos'],
                'create'   => ['Crear',    'Crear permiso'],
                'edit'     => ['Editar',   'Editar permiso'],
                'destroy'  => ['Eliminar', 'Eliminar permiso'],
                'search'   => ['Buscar',   'Buscar permiso'],
            ],
        ],

        'pedidos' => [
            'label'   => 'Pedidos',
            'actions' => [
                'enable'    => ['Habilitar',   'Habilitar pedidos'],
                'kanban'    => ['Kanban',    'Ver kanban pedidos'],
                'listar'    => ['Listado',   'Ver listado de pedidos'],
                'anulados'  => ['Anulados', 'Ver pedidos anulados'],
                'ver_recibidos'  => ['Ver recibidos', 'Ver pedidos recibidos'],
                'ver_cocinando'  => ['Ver cocinando', 'Ver pedidos cocinando'],
                'ver_terminados'  => ['Ver terminados', 'Ver pedidos terminados'],
                'cambiar_estado'  => ['Cambiar estados', 'Cambiar estados pedidos'],
                'imprimir_comanda'  => ['Imprimir comanda', 'Imprimir comanda'],
                'imprimir_boleta'  => ['Imprimir boleta', 'Imprimir boleta'],
                'ver_detalles'  => ['Ver detalles', 'Ver detalles'],
                'ver_datos'  => ['Ver datos', 'Ver datos'],
                'ver_rutas'  => ['Ver rutas', 'Ver rutas'],
                'anular_orden'  => ['Anular orden', 'Anular orden'],
                'activar_orden'  => ['Anular orden', 'Anular orden'],
                'enviar_whatsapp'  => ['Enviar mensaje whatsapp', 'Enviar mensaje whatsapp'],
            ],
        ],

        'caja' => [
            'label'   => 'Caja',
            'actions' => [
                'enable'            => ['Habilitar',   'Habilitar caja'],
                'ver_caja'          => ['Ver cajas',    'Ver cajas'],
                'abrir'             => ['Abrir',   'Abrir cajas'],
                'cerrar'            => ['Cerrar', 'Cerrar cajas'],
                'imprimir_boleta'   => ['Imprimir boleta', 'Imprimir boleta'],
                'crear_ingreso'     => ['Crear ingreso', 'Crear ingreso'],
                'crear_egreso'      => ['Crear egreso', 'Crear egreso'],
                'regularize'        => ['Regularizar ingreso', 'Regularizar ingreso'],
                'ver_movimientos'   => ['Ver movimientos', 'Ver movimientos'],
            ],
        ],

        'locales' => [
            'label'   => 'Locales',
            'actions' => [
                'enable_tiendas'            => ['Habilitar tiendas',   'Habilitar tiendas'],
                'enable_zonas'              => ['Habilitar zonas',    'Habilitar zonas'],
                'listar_tiendas'            => ['Listar tiendas',   'Listar tiendas'],
                'listar_zonas'              => ['Listar zonas',   'Listar zonas'],
                'crear_tiendas'             => ['Crear tiendas', 'Crear tiendas'],
                'abrir_cerrar_tiendas'      => ['Abrir o Cerrar tiendas', 'Abrir o Cerrar tiendas'],
                'crear_zonas'               => ['Crear zonas','Crear zonas'],
                'gestionar_zonas'           => ['Gestionar zonas','Gestionar zonas'],
                'editar_tiendas'            => ['Editar tiendas','Editar tiendas'],
                'cambiar_estado_tienda'     => ['Cambiar estado tiendas','Cambiar estado tiendas'],
                'ver_zona'                  => ['Ver zonas','Ver zonas'],
                'set_precio'                => ['Colocar precio zonas','Colocar precio zonas'],
            ],
        ],

        'espacios' => [
            'label'   => 'Espacios',
            'actions' => [
                'enable_salas'   => ['Habilitar salas', 'Habilitar salas'],
                'enable_mesas'   => ['Habilitar mesas', 'Habilitar mesas'],
                'configurar_mesas'   => ['Configurar mesas', 'Configurar mesas'],
            ],
        ],

        'salas' => [
            'label'   => 'Salas',
            'actions' => [
                'list'     => ['Listar salas',   'Listar salas'],
                'create'   => ['Crear salas',    'Crear sala'],
                'edit'     => ['Editar salas',   'Editar sala'],
                'destroy'  => ['Eliminar salas', 'Eliminar (soft delete) sala'],
                'restore'  => ['Restaurar salas','Restaurar sala eliminada'],
            ],
        ],

        'mesas' => [
            'label'   => 'Mesas',
            'actions' => [
                'list'     => ['Listar mesas',   'Listar mesas'],
                'create'   => ['Crear mesas',    'Crear mesa'],
                'edit'     => ['Editar mesas',   'Editar mesa'],
                'destroy'  => ['Eliminar mesas', 'Eliminar (soft delete) mesa'],
                'restore'  => ['Restaurar mesas','Restaurar mesa eliminada'],
                'abrir'    => ['Abrir mesas',    'Abrir mesa para atención'],
                'cerrar'   => ['Cerrar mesas',   'Cerrar mesa'],
            ],
        ],

        'mantenedores' => [
            'label'   => 'Mantenedores',
            'actions' => [
                'enable'  => ['Habilitar mantenedores',  'Habilitar mantenedores'],
            ],
        ],

        'sliders' => [
            'label'   => 'Sliders',
            'actions' => [
                'enable'          => ['Habilitar sliders',  'Habilitar sliders'],
                'listar'          => ['Listar sliders',  'Listar sliders'],
                'crear'           => ['Crear sliders',  'Crear sliders'],
                'ver'             => ['Ver sliders',  'Ver sliders'],
                'editar'          => ['Editar sliders',  'Editar sliders'],
                'eliminar'        => ['Eliminar sliders',  'Eliminar sliders'],
                'cambiar_estado'  => ['Cambiar estado sliders',  'Cambiar estado sliders'],
            ],
        ],

        'cupones' => [
            'label'   => 'Cupones',
            'actions' => [
                'enable'          => ['Habilitar cupones',  'Habilitar cupones'],
                'listar'          => ['Listar cupones',  'Listar cupones'],
                'crear'           => ['Crear cupones',  'Crear cupones'],
                'editar'          => ['Editar cupones',  'Editar cupones'],
                'cambiar_estado'  => ['Cambiar estado cupones',  'Cambiar estado cupones'],
            ],
        ],

        'productos' => [
            'label'   => 'Productos',
            'actions' => [
                'enable'            => ['Habilitar productos',  'Habilitar productos'],
                'listar'            => ['Listar productos',  'Listar productos'],
                'crear'             => ['Crear productos',  'Crear productos'],
                'ver_eliminados'    => ['Ver productos eliminados',  'Ver productos eliminados'],
                'editar'            => ['Editar productos',  'Editar productos'],
                'desactivar'        => ['Desactivar productos',  'Desactivar productos'],
                'eliminar'          => ['Eliminar productos',  'Eliminar productos'],
                'agregar_opciones'  => ['Agregar opciones productos',  'Agregar opciones productos'],
                'ver_imagen'        => ['Ver imagen',  'Ver imagen'],
            ],
        ],

        'type_products' => [
            'label'   => 'Tipo Productos',
            'actions' => [
                'enable'            => ['Habilitar tipo de productos',  'Habilitar tipo de productos'],
                'listar'            => ['Listar tipo de productos',  'Listar tipo de productos'],
                'crear'             => ['Crear tipo de productos',  'Crear tipo de productos'],
                'editar'            => ['Editar tipo de productos',  'Editar tipo de productos'],
                'cambiar_estado'    => ['Desactivar tipo de productos',  'Desactivar tipo de productos'],
            ],
        ],

        'categorias' => [
            'label'   => 'Categorías',
            'actions' => [
                'enable'            => ['Habilitar categorías',  'Habilitar categorías'],
                'listar'            => ['Listar categorías',  'Listar categorías'],
                'crear'             => ['Crear categorías',  'Crear categorías'],
                'editar'            => ['Editar categorías',  'Editar categorías'],
                'cambiar_estado'    => ['Desactivar categorías',  'Desactivar categorías'],
            ],
        ],

        'rewards' => [
            'label'   => 'Recompensas',
            'actions' => [
                'enable'      => ['Habilitar Hitos',  'Habilitar Hitos'],
                'listar'      => ['Listar Hitos',  'Listar Hitos'],
                'crear'       => ['Crear Hitos',  'Crear Hitos'],
                'editar'      => ['Editar Hitos',  'Editar Hitos'],
                'eliminar'    => ['Eliminar Hitos',  'Eliminar Hitos'],
            ],
        ],

        'reclamos' => [
            'label'   => 'Reclamos',
            'actions' => [
                'enable'        => ['Habilitar Reclamos',  'Habilitar Reclamos'],
                'pendientes'    => ['Listar Reclamos pendientes',  'Listar Reclamos pendientes'],
                'finalizados'   => ['Listar Reclamos finalizados',  'Listar Reclamos finalizados'],
                'gestionar'     => ['Gestionar Reclamos',  'Gestionar Reclamos'],
                'revisar'       => ['Revisar Reclamos',  'Revisar Reclamos'],
            ],
        ],


        // Ejemplo sin CRUD clásico
        /*'graficos' => [
            'label'   => 'Gráficos',
            'actions' => [
                'ver_resumen'  => ['Ver Resumen',  'Ver resumen de ventas'],
                'ver_detalles' => ['Ver Detalles', 'Ver detalle de métricas'],
                'exportar'     => ['Exportar',     'Exportar datos de gráficos'],
            ],
        ],*/

        // Agrega aquí nuevos módulos…
    ],
];