<?php

return [
    'orbit' => 'Orbit',
    'activity_log' => [
        'title' => 'Registro de actividad',
        'user_id' => 'Usuario',
        'user_name' => 'Usuario',
        'event' => 'Acción',
        'subject_type' => 'Modelo',
        'models' => [
            'User' => 'Usuario',
            'Role' => 'Rol',
            'Setting' => 'Configuración',
        ],
        'changes' => 'Cambios',
        'created_at' => 'Fecha',
        'system' => 'Sistema',
        'events' => [
            'created' => 'Creado',
            'updated' => 'Actualizado',
            'deleted' => 'Eliminado',
            'forceDeleted' => 'Eliminado permanentemente',
            'restored' => 'Restaurado',
        ],
    ],
    'settings' => [
        'title' => 'Configuración',
        'branding' => 'Marca',
        'appearance' => 'Apariencia',
        'app_name' => 'Nombre de la aplicación',
        'logo' => 'Logo',
        'logo_hint' => 'JPG, PNG o WebP · máx. 4 MB',
        'palette' => 'Paleta de colores',
        'layout' => 'Diseño',
        'layout_hint' => 'Sidebar: menú lateral · Topbar: menú superior',
        'save' => 'Guardar configuración',
        'saved' => 'Configuración guardada',
        'forbidden' => 'Solo los super administradores pueden cambiar esta configuración',
    ],
];
