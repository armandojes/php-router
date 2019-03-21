<?php

class Router {

  private $request = [];         //request del cliente
  private $routes = [];          //rutas definidas
  private $routeMatch = null;    //ruta matched

  // h e l p e r s
  //convierte cada parte en objetos string -> {param: true || false, path}
  private function routePartToObject ($parts){
    $partsModify = [];
    foreach ($parts as $part) {
      $existParam = preg_match('/\{.+\}/', $part) ? true : false;
      array_push($partsModify, (object) [
        'path' => $part,
        'param' => $existParam,
      ]);
    }
    return $partsModify;
  }



  // p r i v  a t e   f u n c  t i o n s

  //mapea la ruta de entrada y crea this->request
  private function map_request(){
    $this->request = (object) [
      'path' => $_GET['path'] === '' ? '/' : '/'.$_GET['path'],
      'method' => $_SERVER['REQUEST_METHOD'],
    ];
  }

  //mapea las rutas sin parametros para buscar coincidencias
  private function map_sigle_routes(){
    foreach ($this->routes as $route) {
      if  ($this->request->method === $route->method && $this->request->path === $route->path){
        $this->routeMatch = $route;
        return true;
      }
    }
  }

  //agsrega una nueva ruta en la cola de rutas
  private function add_route ($path, $action, $method){
    $params = preg_match('/{[a-z0-9]+}/', $path) ? true : false;
    $parts = explode('/', substr($path, 1));
    $parts = $this->routePartToObject($parts);

    $route = (object) [
      'path' => $path,
      'action' => $action,
      'method' => $method,
      'params' => $params,
      'parts' => $parts,
    ];
    array_push($this->routes, $route);
  }


  public function map_routes(){
    foreach ($this->routes as $route) {
      var_dump($route);
      echo "-------------------";
    }
  }



  // p u b l i c   f u n c t i o n s

  public function get($path, $action){
    $this->add_route($path, $action, 'GET');
  }

  public function dispatch(){
    $this->map_request();
    //$this->map_sigle_routes();
    $this->map_routes();

    //$this->routeMatch->action->__invoke('state');
    // echo "matched ..................";
    // var_dump($this->routeMatch);
    //echo json_encode($this->routes);
  }
}
