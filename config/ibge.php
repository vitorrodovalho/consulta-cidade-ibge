<?php

return [
    'integration' => [
        'host' => env('IBGE_REST_INTEGRATION_HOST', 'http://servicodados.ibge.gov.br/api/v1/'),
        'states' => env('IBGE_REST_INTEGRATION_PATH_STATES', '/localidades/estados'),
        'cities' => env('IBGE_REST_INTEGRATION_PATH_CITIES', ''),
        'prefix' => env('IBGE_REST_INTEGRATION_PATH_CITIES_PREFIX', ''),
    ],
];
