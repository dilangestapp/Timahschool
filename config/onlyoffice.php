<?php

return [
    'document_server_url' => rtrim((string) env('ONLYOFFICE_DOCUMENT_SERVER_URL', 'https://office.timahacademy.online'), '/'),
    'jwt_secret' => (string) env('ONLYOFFICE_JWT_SECRET', ''),
    'jwt_header' => (string) env('ONLYOFFICE_JWT_HEADER', 'Authorization'),
    'jwt_prefix' => (string) env('ONLYOFFICE_JWT_PREFIX', 'Bearer '),
    'enabled' => (bool) env('ONLYOFFICE_ENABLED', true),
];
