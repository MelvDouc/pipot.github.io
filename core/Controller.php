<?php

namespace app\core;

use app\models\forms\Form;
use app\models\User;
use app\models\Product;

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

  protected function hasSessionUser(): bool
  {
    return Application::$instance->session->hasUser();
  }

  protected function getSessionUser(): array | false
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

  protected function findProductById(int $id): array | false
  {
    return Application::$instance
      ->database
      ->findProductById($id);
  }
}
