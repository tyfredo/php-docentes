<?php
    class Router{
        private array $routes =[];

        public function get(string $path, callable $handler) : void {
            $this->routes['GET'][$path] = $handler;
        }
        public function post(string $path, callable $handler) : void {
            $this->routes['POST'][$path] = $handler;
        }
        public function patch(string $path, callable $handler) : void {
            $this->routes['PATCH'][$path] = $handler;
        }
        public function delete(string $path, callable $handler) : void {
            $this->routes['DELETE'][$path] = $handler;
        }

        public function dispatch(): void {

    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $base = '/php-docentes/public';
    $path = str_starts_with($uri, $base)
      ? substr($uri, strlen($base))
      : $uri;

    if ($path === '') {
      $path = '/';
    }

    foreach ($this->routes[$method] ?? [] as $route => $handler) {
      $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $route);
      $pattern = '#^' . $pattern . '$#';

      if (preg_match($pattern, $path, $matches)) {
        array_shift($matches);
        call_user_func_array($handler, $matches);
        return;
      }
    }

    Response::json([
      'message' => 'Ruta no encontrada',
      'path' => $path
    ], 404);
    }
  
}


?>