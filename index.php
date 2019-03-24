<?php

require 'Router.php';

$Router = new Router();
$Router->get('/test', function (){
  echo "get /tests";
});
$Router->post('/test', function (){
  echo "post /tests";
});
$Router->put('/test', function (){
  echo "put /tests";
});
$Router->delete('/test', function (){
  echo "delete /tests";
});
$Router->update('/test', function (){
  echo "update /tests";
});
$Router->patch('/test', function (){
  echo "patch /tests";
});

$Router->dispatch();
