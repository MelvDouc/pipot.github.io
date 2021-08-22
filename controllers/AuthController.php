<?php

namespace app\controllers;

use app\core\Request;
use app\core\Controller;
use app\core\Application;
use app\models\FormGroup;
use app\models\Login;

class AuthController extends Controller
{
  public function login(Request $request)
  {
    if (Application::$instance->session->hasUser())
      return $this->redirect("accueil", "home", [
        "flash_message" => "Vous êtes déjà connecté."
      ]);

    $formGroups = [
      new FormGroup("Nom d'utilisateur ou adresse email", "uuid", "text"),
      new FormGroup("Mot de passe", "password", "password"),
    ];

    if ($request->isPost()) {
      $login = new Login($_POST);
      $validation = $login->validate();

      if ($validation !== 1)
        return $this->render("authentication/login", [
          "formGroups" => $formGroups,
          "error" => $validation
        ]);

      $login->setLoggedUser();
      return $this->redirect("mon-profil", "user/my-profile", [
        "user" => $login->getUser()
      ]);
    }

    return $this->render("authentication/login", [
      "formGroups" => $formGroups
    ]);
  }

  public function logout()
  {
    Application::$instance->session->removeUser();
    $this->redirect("", "home");
  }
}
