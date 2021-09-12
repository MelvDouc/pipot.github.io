<?php

namespace app\core;

use Pug\Pug;

class Router
{

  public Request $request;
  public Response $response;
  public Pug $viewEngine;
  protected array $routes = [];

  public function __construct(Request $request, Response $response)
  {
    $this->request = $request;
    $this->response = $response;
    $this->viewEngine = new Pug([]);
  }

  public function get(string $path, array $callback): void
  {
    $this->routes["get"][$path] = $callback;
  }

  public function post(string $path, array $callback): void
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

  public function renderView(string $view, array $params = [])
  {
    // $loader = new \Twig\Loader\FilesystemLoader(Application::$ROOT_DIR  . "/pages");
    // $twig = new \Twig\Environment($loader);
    // $twig->addExtension(new StringExtension());
    // $appUser = Application::$instance->session->getUser();
    // $twig->addGlobal("appUser", $appUser);

    // echo $twig->render("$view.html.twig", $params);
    $path = Application::$ROOT_DIR . "/pages";
    $this->viewEngine->displayFile("$path/$view.pug", $params);
  }
}
