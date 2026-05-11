<?php

$env = require __DIR__ . '/env.php';

$projectId = trim($env['FIREBASE_PROJECT_ID']);

return [
  'project_id' => $projectId,
  'client_email' => trim($env['FIREBASE_CLIENT_EMAIL']),
  'private_key' => $env['FIREBASE_PRIVATE_KEY'],

  'scope' => 'https://www.googleapis.com/auth/datastore',
  'token_uri' => 'https://oauth2.googleapis.com/token',

  'firestore_base_url' => 'https://firestore.googleapis.com/v1/projects/'
    . $projectId
    . '/databases/(default)/documents',
];