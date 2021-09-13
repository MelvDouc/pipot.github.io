<?php

namespace app\controllers;

use app\core\Request;
use app\core\Controller;
use app\core\Application;
use app\models\User;

class AuthController extends Controller
{
  public function login(Request $request)
  {
    if ($this->getSessionUser())
      return $this->redirectHome([
        "error_message" => "Vous êtes déjà connecté."
      ]);

    if ($request->isGet())
      return $this->render("authentication/login", [
        "title" => "Connexion"
      ]);

    $uuid = $_POST["uuid"] ?? null;
    $user = User::findOne(["username" => $uuid, "email" => $uuid], "OR");

    if (!$user)
      return $this->render("authentication/login", [
        "title" => "Connexion",
        "flashErrors" => "Identifiants incorrects."
      ]);

    $user->setPasswords("plain", $_POST["password"] ?? null);
    if (!$user->comparePassword())
      return $this->render("authentication/login", [
        "title" => "Connexion",
        "flashErrors" => "Identifiants incorrects."
      ]);

    Application::$instance->session->setUser($user);
    return $this->redirect("mon-profil", "user/profile", [
      "title" => "Profil de $user->username"
    ]);
  }

  public function logout()
  {
    Application::$instance->session->removeUser();
    return $this->redirectHome();
  }
}
