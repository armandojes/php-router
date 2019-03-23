<?php

class Router {

  private $request = [];         //request del cliente
  private $routes = [];          //rutas definidas
  private $routeMatch = null;    //ruta matched


  // h e l p e r s
  //convierte cada parte en objetos string -> {param: true || false, path}
  private function routePartToObject ($path){
    $params = preg_match('/{[a-z0-9]+}/', $path) ? true : false;
    $ArrayOfparts = explode('/', substr($path, 1));
    $partsModify = [];
    foreach ($ArrayOfparts as $part) {
      $existParam = preg_match('/\{.+\}/', $part) ? true : false;
      array_push($partsModify, (object) [
        'path' => $part,
        'param' => $existParam,
      ]);
    }
    return $partsModify;
  }

  //verificar cada parte de una ruta
  private function verify_part ($partRoutes, $partRequest){
    if (!$partRoutes->param) return $partRoutes->path === $partRequest->path;
    return preg_match('/\{[a-zA-Z0-9]+\}/', $partRoutes->path);
  }

  // p r i v  a t e   f u n c  t i o n s

  //mapea la ruta de entrada y crea this->request
  private function map_request(){
    $path = $_GET['path'] === '' ? '/' : '/'.$_GET['path'];
    $this->request = (object) [
      'path' => $path,
      'method' => $_SERVER['REQUEST_METHOD'],
      'parts' => $this->routePartToObject($path),
    ];
  }

  //agrega una nueva ruta en la cola de rutas
  private function add_route ($path, $action, $method){
    $route = (object) [
      'path' => $path,
      'action' => $action,
      'method' => $method,
      'parts' => $this->routePartToObject($path),
    ];
    array_push($this->routes, $route);
  }

  //mapear todas las rutas para encontrar coincidencias y muta match routeMatch
  private function map_routes(){
    foreach ($this->routes as $route) {
      $isMatched = $this->Verific_route($route);
      if ($isMatched){
        $this->routeMatch = $route;
        return true;
      }
    }
  }

  //extact variables ($this->routeMatch) : [...params]
  private function extract_variables(){
    if (!$this->routeMatch) return [];
    $existParams = preg_match('/\{[a-zA-Z0-9]+\}/', $this->routeMatch->path);
    if (!$existParams) { $this->routeMatch->params = []; return [];}
    $params = [];
    foreach ($this->routeMatch->parts as $index => $part) {
      if ($part->param) $params = array_merge($params,[preg_replace('/[\{\}]/','',$part->path) => $this->request->parts[$index]->path]);
    }
    $this->routeMatch->params = (object) $params;
    return $params;
  }


  //mapea cada ruta return true || false;
  private function Verific_route($route){
    if ($route->method != $this->request->method) return false;             //verificar metodo
    if (count($this->request->parts) != count($route->parts)) return false;  //verificar longitud
    foreach ($route->parts as $index => $routeParts) {
      $isMatchedPart = $this->verify_part($routeParts, $this->request->parts[$index]);
      if (!$isMatchedPart) return false;
    }
    return true;
  }

  //ejecutaren caso de no encontrar match
  private function no_match(){
    echo json_encode([
      'error' => true,
      'errorCode' => 404,
      'errorMessaje' => 'recurso no encontrado',
      'path' => $this->request
    ]);
  }


  // p u b l i c   f u n c t i o n s

  public function get($path, $action){
    $this->add_route($path, $action, 'GET');
  }

  public function dispatch(){
    $this->map_request();
    $this->map_routes();
    $this->extract_variables();
    $this->routeMatch
      ? $this->routeMatch->action->__invoke($this->routeMatch->params)
      : $this->no_match();
  }
}
