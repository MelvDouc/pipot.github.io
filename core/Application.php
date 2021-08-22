<?php

namespace app\core;

class Application {
  public static $instance;
  public static $ROOT_DIR;
  public $router;
  public $request;
  public $response;
  public $session;
  public $database;
  private $controller;

  public function __construct($rootPath) {
    self::$instance = $this;
    self::$ROOT_DIR = $rootPath;
    $this->request = new Request();
    $this->response = new Response();
    $this->session = new Session();
    $this->router = new Router($this->request, $this->response);
    $this->database = new Database();
    $this->database->connect();
  }

  public static function vardump($var) {
    echo "<pre>";
    var_dump($var);
    echo "</pre>";
  }

  public function get($path, $callback)
  {
    $this->router->get($path, $callback);
  }

  public function post($path, $callback)
  {
    $this->router->post($path, $callback);
  }
  
  public function run()
  {
    $this->router->resolve();
  }

  public function getController()
  {
    return $this->controller;
  }

  public function setController($value)
  {
    $this->controller = $value;
  }
}