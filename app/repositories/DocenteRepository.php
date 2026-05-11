<?php

require_once __DIR__ . '/../core/FirestoreClient.php';

class DocenteRepository
{
  private FirestoreClient $client;
  private string $collection = '/docentes';

  public function __construct()
  {
    $this->client = new FirestoreClient();
  }

  private function toFirestoreFields(array $data): array
  {
    $fields = [];

    foreach ($data as $key => $value) {
      if (is_bool($value)) {
        $fields[$key] = ['booleanValue' => $value];
      } elseif (is_int($value)) {
        $fields[$key] = ['integerValue' => $value];
      } elseif (is_float($value)) {
        $fields[$key] = ['doubleValue' => $value];
      } else {
        $fields[$key] = ['stringValue' => (string)$value];
      }
    }

    return ['fields' => $fields];
  }

  private function fromFirestoreDocument(array $document): array
  {
    $name = $document['name'] ?? '';
    $id = basename($name);
    $fields = $document['fields'] ?? [];

    $data = [
      'id' => $id
    ];

    foreach ($fields as $key => $value) {
      if (isset($value['stringValue'])) {
        $data[$key] = $value['stringValue'];
      } elseif (isset($value['booleanValue'])) {
        $data[$key] = $value['booleanValue'];
      } elseif (isset($value['integerValue'])) {
        $data[$key] = (int)$value['integerValue'];
      } elseif (isset($value['doubleValue'])) {
        $data[$key] = (float)$value['doubleValue'];
      } elseif (isset($value['timestampValue'])) {
        $data[$key] = $value['timestampValue'];
      }
    }

    return $data;
  }

  public function findAll(): array
  {
    $response = $this->client->request('GET', $this->collection);

    $documents = $response['documents'] ?? [];

    $items = array_map(function ($doc) {
      return $this->fromFirestoreDocument($doc);
    }, $documents);

    $items = array_values(array_filter($items, function ($item) {
      return ($item['activo'] ?? true) === true;
    }));

    return $items;
  }

  public function findById(string $id): ?array
  {
    $response = $this->client->request('GET', $this->collection . '/' . $id);

    if (isset($response['error'])) {
      return null;
    }

    return $this->fromFirestoreDocument($response);
  }

  public function findByUsuario(string $usuario): ?array
  {
    $items = $this->findAll();

    foreach ($items as $item) {
      if (($item['usuario'] ?? '') === $usuario) {
        return $item;
      }
    }

    return null;
  }

 public function create(array $data): array
{
  $response = $this->client->request(
    'POST',
    $this->collection,
    $this->toFirestoreFields($data)
  );

  if (isset($response['error'])) {
    throw new Exception(
      $response['error']['message'] ?? 'Error al crear docente en Firestore'
    );
  }

  if (!isset($response['name']) || !isset($response['fields'])) {
    throw new Exception(
      'Firestore no devolvió un documento válido: ' . json_encode($response, JSON_UNESCAPED_UNICODE)
    );
  }

  return $this->fromFirestoreDocument($response);
}

  public function update(string $id, array $data): ?array
  {
    $updateMask = [];

    foreach (array_keys($data) as $field) {
      $updateMask[] = 'updateMask.fieldPaths=' . urlencode($field);
    }

    $query = count($updateMask) > 0 ? '?' . implode('&', $updateMask) : '';

    $response = $this->client->request(
      'PATCH',
      $this->collection . '/' . $id . $query,
      $this->toFirestoreFields($data)
    );

    if (isset($response['error'])) {
      return null;
    }

    return $this->fromFirestoreDocument($response);
  }

  public function softDelete(string $id): ?array
  {
    return $this->update($id, [
      'activo' => false,
      'updatedAt' => date('c')
    ]);
  }

  public function toggleActive(string $id, bool $activo): ?array
  {
    return $this->update($id, [
      'activo' => $activo,
      'updatedAt' => date('c')
    ]);
  }
}