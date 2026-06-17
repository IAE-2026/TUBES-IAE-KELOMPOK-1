<?php

return [
    'sso' => [
        'base_url' => env('SSO_BASE_URL', 'https://iae-sso.virtualfri.id'),
        'email' => env('SSO_EMAIL'),
        'password' => env('SSO_PASSWORD'),
        'jwks_url' => env('SSO_BASE_URL', 'https://iae-sso.virtualfri.id') . '/api/v1/auth/jwks',
        'token_url' => env('SSO_BASE_URL', 'https://iae-sso.virtualfri.id') . '/api/v1/auth/token',
    ],
    'soap' => [
        'audit_url' => env('SOAP_AUDIT_URL', 'https://iae-sso.virtualfri.id/soap/v1/audit'),
    ],
    'rabbitmq' => [
        'publish_url' => env('RABBITMQ_PUBLISH_URL', 'https://iae-sso.virtualfri.id/api/v1/messages/publish'),
    ],
    'team_id' => env('TEAM_ID', 'TEAM-28'),
];
