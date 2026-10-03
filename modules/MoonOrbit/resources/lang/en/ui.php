<?php

return [
    'orbit' => 'Orbit',
    'activity_log' => [
        'title' => 'Activity log',
        'user_id' => 'User',
        'user_name' => 'User',
        'event' => 'Action',
        'subject_type' => 'Model',
        'models' => [
            'User' => 'User',
            'Role' => 'Role',
            'Setting' => 'Setting',
        ],
        'changes' => 'Changes',
        'created_at' => 'Date',
        'system' => 'System',
        'events' => [
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'forceDeleted' => 'Deleted permanently',
            'restored' => 'Restored',
        ],
    ],
    'settings' => [
        'title' => 'Settings',
        'branding' => 'Branding',
        'appearance' => 'Appearance',
        'app_name' => 'Application name',
        'logo' => 'Logo',
        'logo_hint' => 'JPG, PNG or WebP · max 4 MB',
        'palette' => 'Color palette',
        'layout' => 'Layout',
        'layout_hint' => 'Sidebar: side menu · Topbar: top menu',
        'save' => 'Save settings',
        'saved' => 'Settings saved',
        'forbidden' => 'Only super admins can change these settings',
    ],
];
