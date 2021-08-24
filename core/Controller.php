<?php

namespace app\core;

class Controller
{
  public function render(string $page, array $params = []): void
  {
    Application::$instance->router->renderView($page, $params);
  }

  protected function redirect(string $location, string $page, array $params = [])
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

  protected function redirectNotFound(array $params = [])
  {
    return $this->render("404", $params);
  }

  protected function isUserProfile(int $database_id): bool
  {
    if (!Application::$instance->session->hasUser())
      return false;
    return (bool)($database_id === (int)Application::$instance->session->getUser()["id"]);
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
