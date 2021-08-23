<?php

namespace app\core;

use Twig\Extra\String\StringExtension;

class Router
{

  public Request $request;
  public Response $response;
  protected $routes = [];

  public function __construct($request, $response)
  {
    $this->request = $request;
    $this->response = $response;
  }

  public function get($path, $callback)
  {
    $this->routes["get"][$path] = $callback;
  }

  public function post($path, $callback)
  {
    $this->routes["post"][$path] = $callback;
  }

  public function resolve()
  {
    $path = $this->request->getPath();
    $method = $this->request->getMethod();
    $callback = $this->routes[$method][$path] ?? false;

    if (!$callback) {
      $this->response->setStatusCode(404);
      return $this->renderView("404");
    }

    if (is_string($callback))
      return $this->renderView($callback);

    if (is_array($callback)) {
      Application::$instance->setController(new $callback[0]());
      $callback[0] = Application::$instance->getController();
    }

    call_user_func($callback, $this->request);
  }

  public function renderView($view, $params = [])
  {
    $loader = new \Twig\Loader\FilesystemLoader(Application::$ROOT_DIR  . "/pages");
    $twig = new \Twig\Environment($loader);
    $twig->addExtension(new StringExtension());

    $params["is_user_logged_in"] = Application::$instance->session->hasUser();

    echo $twig->render("$view.html.twig", $params);
  }
}
