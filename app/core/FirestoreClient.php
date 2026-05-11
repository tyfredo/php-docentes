<?php

require_once __DIR__ . '/FirebaseToken.php';

class FirestoreClient
{
  private string $baseUrl;
  private FirebaseToken $tokenService;

  public function __construct()
  {
    $config = require __DIR__ . '/../config/firebase.php';

    $this->baseUrl = $config['firestore_base_url'];
    $this->tokenService = new FirebaseToken();
  }

  public function request(string $method, string $path = '', ?array $body = null): array
  {
    $token = $this->tokenService->getAccessToken();

    $url = $this->baseUrl . $path;

    $headers = [
      'Authorization: Bearer ' . $token,
      'Content-Type: application/json'
    ];

    $options = [
      'http' => [
        'method' => $method,
        'header' => implode("\r\n", $headers),
        'ignore_errors' => true
      ]
    ];

    if ($body !== null) {
      $options['http']['content'] = json_encode($body, JSON_UNESCAPED_UNICODE);
    }

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    if ($response === false) {
      throw new Exception('No se pudo conectar con Firestore');
    }

    $json = json_decode($response, true);

    if (!is_array($json)) {
      throw new Exception('Respuesta inválida de Firestore: ' . $response);
    }

    return $json;
  }
}