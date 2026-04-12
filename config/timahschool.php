<?php

return [
    'admin_path' => trim((string) (
        env('TIMAHSCHOOL_ADMIN_PATH')
        ?: env('ADMIN_PATH')
        ?: 'backoffice-access'
    ), '/'),

    'admin_access_code' => (string) (
        env('TIMAHSCHOOL_ADMIN_ACCESS_CODE')
        ?: env('ADMIN_ACCESS_CODE')
        ?: env('BACKOFFICE_ACCESS_CODE')
        ?: ''
    ),
];