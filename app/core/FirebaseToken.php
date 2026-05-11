<?php

class FirebaseToken
{
  private array $config;

  public function __construct()
  {
    $this->config = require __DIR__ . '/../config/firebase.php';
  }

  private function base64UrlEncode(string $data): string
  {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }

  private function getPrivateKey()
  {
    $privateKey = $this->config['private_key'] ?? '';

    $privateKey = str_replace('\\n', "\n", $privateKey);

    $resource = openssl_pkey_get_private($privateKey);

    if ($resource === false) {
      throw new Exception(
        'La llave privada de Firebase no es válida. Revisa FIREBASE_PRIVATE_KEY en app/config/env.php'
      );
    }

    return $resource;
  }

  public function getAccessToken(): string
  {
    if (!extension_loaded('openssl')) {
      throw new Exception('La extensión OpenSSL no está habilitada en PHP/MAMP');
    }

    $now = time();

    $header = [
      'alg' => 'RS256',
      'typ' => 'JWT'
    ];

    $payload = [
      'iss' => $this->config['client_email'],
      'scope' => $this->config['scope'],
      'aud' => $this->config['token_uri'],
      'iat' => $now,
      'exp' => $now + 3600
    ];

    $base64Header = $this->base64UrlEncode(json_encode($header));
    $base64Payload = $this->base64UrlEncode(json_encode($payload));

    $unsignedJwt = $base64Header . '.' . $base64Payload;

    $privateKey = $this->getPrivateKey();

    $signature = '';

    $signed = openssl_sign(
      $unsignedJwt,
      $signature,
      $privateKey,
      OPENSSL_ALGO_SHA256
    );

    if (!$signed || empty($signature)) {
      throw new Exception('No se pudo firmar el JWT con OpenSSL');
    }

    $jwt = $unsignedJwt . '.' . $this->base64UrlEncode($signature);

    $postData = http_build_query([
      'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
      'assertion' => $jwt
    ]);

    $context = stream_context_create([
      'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $postData,
        'ignore_errors' => true
      ]
    ]);

    $response = file_get_contents($this->config['token_uri'], false, $context);

    if ($response === false) {
      throw new Exception('No se pudo conectar con Google OAuth');
    }

    $json = json_decode($response, true);

    if (!isset($json['access_token'])) {
      throw new Exception(
        'No se pudo obtener el token de Firebase: ' . $response
      );
    }

    return $json['access_token'];
  }
}