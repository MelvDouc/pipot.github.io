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
    $userId = Application::$instance->session->getUserId();
    if (!$userId) return null;
    return User::findOne(["id" => $userId]);
  }

  protected function isLoggedAsUser(): bool
  {
    if (!$this->getSessionUser()) return false;
    return $this->getSessionUser()->role === "USER";
  }

  protected function isLoggedAsAdmin(): bool
  {
    if (!$this->getSessionUser()) return false;
    return $this->getSessionUser()->role === "ADMIN";
  }

  protected function getFlash($key): string|array|null
  {
    $flash = Application::$instance->session->getFlash($key);
    Application::$instance->session->removeFlash($key);
    return $flash;
  }

  protected function setFlash(string $key, string|array $messages): void
  {
    Application::$instance->session->setFlash($key, $messages);
  }
}
