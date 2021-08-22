<?php

namespace app\core;

class Controller
{
  public function render($page, $params = [])
  {
    Application::$instance->router->renderView($page, $params);
  }

  protected function redirect($location, $page, $params = [])
  {
    header("Location: $location");
    return $this->render($page, $params);
  }

  protected function redirectNotFound($params = [])
  {
    return $this->render("404", $params);
  }

  protected function getParamId()
  {
    $id = explode("/", $_SERVER["REQUEST_URI"])[2];
    if (!preg_match("/\d+/", $id))
      return null;
    return (int)$id;
  }
}