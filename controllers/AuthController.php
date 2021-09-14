<?php

namespace app\controllers;

use app\core\Request;
use app\core\Controller;
use app\core\Application;
use app\models\User;

class AuthController extends Controller
{
  private function get()
  {
    return $this->render("authentication/login", [
      "title" => "Connexion",
      "flashErrors" => $this->getFlash("errors")
    ]);
  }

  public function login(Request $request)
  {
    if ($this->getSessionUser())
      $this->setFlash("errors", ["Vous êtes déjà connecté."]);
    return $this->redirectHome();

    if ($request->isGet())
      return $this->get();

    $uuid = $_POST["uuid"] ?? null;
    $user = User::findOne(["username" => $uuid, "email" => $uuid], "OR");

    if (!$user) {
      $this->setFlash("errors", ["Identifiants incorrects."]);
      return $this->get();
    }

    $user->setPasswords("plain", $_POST["password"] ?? null);
    if (!$user->comparePassword()) {
      $this->setFlash("errors", ["Identifiants incorrects."]);
      return $this->get();
    }

    Application::$instance->session->setUser($user);
    return $this->redirect("mon-profil");
  }

  public function logout()
  {
    Application::$instance->session->removeUser();
    return $this->redirectHome();
  }
}
