<?php

namespace app\core;

use app\models\User;

class Controller
{
  public function render(string $page, array $params = []): void
  {
    Application::$instance->router->renderView($page, $params);
  }

  protected function redirect(string $location, ?string $page = null, array $params = [])
  {
    if ($page)
      $this->render($page, $params);
    return header("Location: $location");
  }

  protected function redirectHome(array $params = [])
  {
    return $this->redirect("/accueil", "home/home", $params);
  }

  protected function redirectToLogin()
  {
    return $this->redirect("/connexion", "authentication/login", [
      "error" => "Vous n'êtes pas connecté."
    ]);
  }

  protected function redirectNotFound(array $params = [])
  {
    return $this->render("404", $params);
  }

  protected function getSessionUser(): ?User
  {
    return Application::$instance->session->getUser();
  }

  protected function isLoggedAsUser(): bool
  {
    return $this->getSessionUser()
      && (int)$this->getSessionUser()->role === "USER";
  }

  protected function isLoggedAsAdmin(): bool
  {
    return $this->getSessionUser()
      && (int)$this->getSessionUser()->role === "ADMIN";
  }
}
