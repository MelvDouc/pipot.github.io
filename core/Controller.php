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
    return $this->redirect("/accueil", "home", $params);
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

  private function hasSessionUser(): bool
  {
    return Application::$instance->session->hasUser();
  }

  protected function getSessionUser(): ?array
  {
    return Application::$instance->session->getUser();
  }

  protected function isLoggedAsUser(): bool
  {
    return $this->hasSessionUser()
      && (int)$this->getSessionUser()["role"] === User::ROLES["USER"];
  }

  protected function isLoggedAsAdmin(): bool
  {
    return $this->hasSessionUser()
      && (int)$this->getSessionUser()["role"] === User::ROLES["ADMIN"];
  }

  protected function findUserById(int $id): ?array
  {
    return Application::$instance
      ->database
      ->findOne(User::DB_TABLE, ["*"]);
  }

  protected function findProductById(int $id): ?array
  {
    return Application::$instance
      ->database
      ->findProductById($id);
  }
}
