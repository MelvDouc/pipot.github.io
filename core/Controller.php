<?php

namespace app\core;

class Controller
{
  public function render($page, $params = [])
  {
    Application::$instance->router->renderView($page, $params);
  }

  protected function redirect($location)
  {
    return header("Location: $location");
  }

  protected function redirectNotFound()
  {
    return $this->render("404");
  }

  protected function getParamId()
  {
    $id = explode("/", $_SERVER["REQUEST_URI"])[2];
    if (!preg_match("/\d+/", $id))
      return null;
    return (int)$id;
  }
}