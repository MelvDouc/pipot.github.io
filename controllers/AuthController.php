<?php

namespace app\controllers;

use app\core\Request;
use app\core\Controller;
use app\core\Application;
use app\models\Form;
use app\models\Login;

class AuthController extends Controller
{
  public function login(Request $request)
  {
    if ($this->hasSessionUser())
      return $this->redirect("/accueil", "home", [
        "error_message" => "Vous êtes déjà connecté."
      ]);

    $form = new Form();
    $form->start("/connexion", false, "login-form");
    $form->add_input("Nom d'utilisateur ou adresse email", "uuid", "text");
    $form->add_input("Mot de passe", "password");
    $form->add_submit("Se connecter");
    $form->end();

    if ($request->isGet())
      return $this->render("authentication/login", [
        "form" => $form->createView()
      ]);

    $login = new Login($_POST);
    $validation = $login->validate();

    if ($validation !== 1)
      return $this->render("authentication/login", [
        "form" => $form->createView(),
        "error_message" => $validation
      ]);

    $login->setLoggedUser();
    return $this->redirect("/mon-profil", "user/my-profile", [
      "user" => $login->getUser()
    ]);
  }

  public function logout()
  {
    Application::$instance->session->removeUser();
    return $this->redirect("/accueil", "home");
  }
}
