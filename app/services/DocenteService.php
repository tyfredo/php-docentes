<?php

require_once __DIR__ . '/../repositories/DocenteRepository.php';
require_once __DIR__ . '/../schemas/DocenteSchema.php';

class DocenteService
{
  private DocenteRepository $repository;

  public function __construct()
  {
    $this->repository = new DocenteRepository();
  }

  public function getAll(): array
  {
    return [
      'items' => $this->repository->findAll()
    ];
  }

  public function getById(string $id): array
  {
    $docente = $this->repository->findById($id);

    if (!$docente || ($docente['activo'] ?? true) === false) {
      throw new Exception('Docente no encontrado');
    }

    return $docente;
  }

  public function create(array $data): array
  {
    $errors = DocenteSchema::validateCreate($data);

    if (!empty($errors)) {
      throw new Exception(json_encode($errors, JSON_UNESCAPED_UNICODE));
    }

    $existingUser = $this->repository->findByUsuario($data['usuario']);

    if ($existingUser) {
      throw new Exception('El usuario ya está registrado');
    }

    $payload = [
      'nombre' => trim($data['nombre']),
      'apaterno' => trim($data['apaterno']),
      'amaterno' => trim($data['amaterno']),
      'direccion' => trim($data['direccion']),
      'telefono' => trim($data['telefono']),
      'ciudad' => trim($data['ciudad']),
      'estado' => trim($data['estado']),
      'usuario' => trim($data['usuario']),
      'password' => password_hash($data['password'], PASSWORD_DEFAULT),
      'activo' => true,
      'createdAt' => date('c'),
      'updatedAt' => date('c')
    ];

    return $this->repository->create($payload);
  }

  public function update(string $id, array $data): array
  {
    $current = $this->repository->findById($id);

    if (!$current) {
      throw new Exception('Docente no encontrado');
    }

    $errors = DocenteSchema::validateUpdate($data);

    if (!empty($errors)) {
      throw new Exception(json_encode($errors, JSON_UNESCAPED_UNICODE));
    }

    $payload = [];

    $allowedFields = [
      'nombre',
      'apaterno',
      'amaterno',
      'direccion',
      'telefono',
      'ciudad',
      'estado',
      'usuario',
      'password',
      'activo'
    ];

    foreach ($allowedFields as $field) {
      if (array_key_exists($field, $data)) {
        if ($field === 'password') {
          $payload[$field] = password_hash($data[$field], PASSWORD_DEFAULT);
        } elseif (is_string($data[$field])) {
          $payload[$field] = trim($data[$field]);
        } else {
          $payload[$field] = $data[$field];
        }
      }
    }

    if (isset($payload['usuario']) && $payload['usuario'] !== ($current['usuario'] ?? '')) {
      $existingUser = $this->repository->findByUsuario($payload['usuario']);

      if ($existingUser) {
        throw new Exception('El usuario ya está registrado');
      }
    }

    $payload['updatedAt'] = date('c');

    $updated = $this->repository->update($id, $payload);

    if (!$updated) {
      throw new Exception('No se pudo actualizar el docente');
    }

    return $updated;
  }

  public function delete(string $id): array
  {
    $current = $this->repository->findById($id);

    if (!$current) {
      throw new Exception('Docente no encontrado');
    }

    $deleted = $this->repository->softDelete($id);

    if (!$deleted) {
      throw new Exception('No se pudo eliminar el docente');
    }

    return $deleted;
  }

  public function toggleActive(string $id, array $data): array
  {
    if (!isset($data['activo']) || !is_bool($data['activo'])) {
      throw new Exception('El campo activo es obligatorio y debe ser booleano');
    }

    $current = $this->repository->findById($id);

    if (!$current) {
      throw new Exception('Docente no encontrado');
    }

    $updated = $this->repository->toggleActive($id, $data['activo']);

    if (!$updated) {
      throw new Exception('No se pudo cambiar el estado del docente');
    }

    return $updated;
  }
}