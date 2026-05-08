<?php
    $env = require __DIR__ . '/env.php';

    return [
        'project_id' => $env['FIREBASE_PROJECT_ID'],
        'client_email' => $env['FIREBASE_CLIENT_EMAIL'],
        'private_key' =>  $env['FIREBASE_PRIVATE_KEY'],
        'scope' => 'https://www.googleapis.com/auth/datastore',
        'token_uri' => 'https://oauth2.googleapis.com/token',
        'firestore_base_url' => 'https://firestore.googleapis.com/v1/projects/' . $env['FIREBASE_PROJECT_ID'] . '/databases/(default)/documents',
    ];

?>