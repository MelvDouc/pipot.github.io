<?php

namespace app\controllers;

use app\core\Request;
use app\core\Controller;
use app\core\Application;
use app\models\Login;

class AuthController extends Controller
{
  public function login(Request $request)
  {
    if ($this->hasSessionUser())
      return $this->redirectHome([
        "error_message" => "Vous êtes déjà connecté."
      ]);

    $form = Login::getForm("/connexion");

    if ($request->isGet())
      return $this->render("authentication/login", [
        "form" => $form->createView()
      ]);

    $login = new Login($request->getBody());
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
    return $this->redirectHome();
  }
}
