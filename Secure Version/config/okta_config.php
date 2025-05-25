<?php
// Okta API Configuration
return [
    'api' => [
        'identifier' => 'https://api.healthcare.local',
        'name' => 'Healthcare Management System API',
        'description' => 'API for the Healthcare Management System',
        'scopes' => [
            'openid',
            'email',
            'profile',
            'patient:read',
            'patient:write',
            'appointment:read',
            'appointment:write'
        ]
    ],
    'oauth' => [
        'client_id' => '0oa8qg6q8q8q8q8q8q8q8',
        'client_secret' => 'YOUR_OKTA_CLIENT_SECRET',
        'redirect_uri' => 'http://localhost/Info/oauth_okta.php',
        'auth_url' => 'https://dev-123456.okta.com/oauth2/default/v1/authorize',
        'token_url' => 'https://dev-123456.okta.com/oauth2/default/v1/token',
        'userinfo_url' => 'https://dev-123456.okta.com/oauth2/default/v1/userinfo'
    ]
]; 