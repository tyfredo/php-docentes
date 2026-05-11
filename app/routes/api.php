<?php

require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../controllers/HealthController.php';
require_once __DIR__ . '/../controllers/DocentesController.php';


$router = new Router();

$healthController = new HealthController();
$docenteController = new DocenteController();

//rutas de health
$router->get('/health', [$healthController, 'index']);
$router->get('/health/firebase', [$healthController, 'firebase']);

//rutas de docente 
$router->get('/docentes', [$docenteController, 'index']);
$router->get('/docentes/{id}', [$docenteController, 'show']);
$router->post('/docentes', [$docenteController, 'store']);
$router->patch('/docentes/{id}', [$docenteController, 'update']);
$router->delete('/docentes/{id}', [$docenteController, 'destroy']);
$router->patch('/docentes/{id}/toggle-active', [$docenteController, 'toggleActive']);


$router->dispatch();

?>