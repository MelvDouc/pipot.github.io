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

  protected function redirectToLogin()
  {
    return $this->redirect("connexion", "authentication/login", [
      "error" => "Vous n'êtes pas connecté."
    ]);
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

  protected function isUserProfile($dbId)
  {
    if (!Application::$instance->session->hasUser())
      return false;
    return (bool)($dbId == Application::$instance->session->getUser()["id"]);
  }

  protected function hasSessionUser(): bool
  {
    return Application::$instance->session->hasUser();
  }

  protected function getSessionUser(): array | false
  {
    return Application::$instance->session->getUser();
  }
}