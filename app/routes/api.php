<?php

require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../controllers/HealthController.php';

$router = new Router();

$healthController = new HealthController();

$router->get('/health', [$healthController, 'index']);
$router->get('/health/firebase', [$healthController, 'firebase']);

$router->dispatch();