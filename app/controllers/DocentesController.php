<?php

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../services/DocenteService.php';

class DocenteController
{
  private DocenteService $service;

  public function __construct()
  {
    $this->service = new DocenteService();
  }

  public function index(): void
  {
    try {
      $result = $this->service->getAll();

      Response::json([
        'message' => 'Docentes obtenidos correctamente',
        'items' => $result['items']
      ]);
    } catch (Exception $e) {
      Response::json([
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function show(string $id): void
  {
    try {
      $docente = $this->service->getById($id);

      Response::json([
        'message' => 'Docente obtenido correctamente',
        'item' => $docente
      ]);
    } catch (Exception $e) {
      Response::json([
        'message' => $e->getMessage()
      ], 404);
    }
  }

  public function store(): void
  {
    try {
      $body = Request::body();
      $docente = $this->service->create($body);

      Response::json([
        'message' => 'Docente creado correctamente',
        'item' => $docente
      ], 201);
    } catch (Exception $e) {
      Response::json([
        'message' => $e->getMessage()
      ], 400);
    }
  }

  public function update(string $id): void
  {
    try {
      $body = Request::body();
      $docente = $this->service->update($id, $body);

      Response::json([
        'message' => 'Docente actualizado correctamente',
        'item' => $docente
      ]);
    } catch (Exception $e) {
      Response::json([
        'message' => $e->getMessage()
      ], 400);
    }
  }

  public function destroy(string $id): void
  {
    try {
      $docente = $this->service->delete($id);

      Response::json([
        'message' => 'Docente eliminado correctamente',
        'item' => $docente
      ]);
    } catch (Exception $e) {
      Response::json([
        'message' => $e->getMessage()
      ], 400);
    }
  }

  public function toggleActive(string $id): void
  {
    try {
      $body = Request::body();
      $docente = $this->service->toggleActive($id, $body);

      Response::json([
        'message' => 'Estado del docente actualizado correctamente',
        'item' => $docente
      ]);
    } catch (Exception $e) {
      Response::json([
        'message' => $e->getMessage()
      ], 400);
    }
  }
}