<?php

namespace app\controllers;

use app\core\Controller;
use app\core\Application;
use app\core\Request;
use app\models\Form;
use app\models\User;

class RegisterController extends Controller
{
  public function register(Request $request)
  {
    if (Application::$instance->session->hasUser())
      return $this->redirect("", "accueil");

    $form = new Form();
    $form->start("/inscription", false, "register-form");
    $form->add_input("Nom d'utilisateur", "username", "text");
    $form->add_input("Adresse email", "email");
    $form->add_input("Mot de passe", "password");
    $form->add_input("Confirmer le mot de passe", "confirm_password", "password");
    $form->add_checkbox("Accepter les conditions d'utilisation", "agree_terms");
    $form->add_submit("S'inscrire");
    $form->end();

    if ($request->isGet())
      return $this->render("registration/index", [
        "form" => $form->createView()
      ]);

    $user = new User($_POST);
    $validation = $user->validate();

    if ($validation !== 1)
      return $this->render("registration/index", [
        "form" => $form->createView(),
        "error" => $validation
      ]);

    $user->save();
    $user->send_verification();

    return $this->render("home", [
      "success_message" => "Nous vous avons envoyé un mail confirmant la création de votre compte. Veuillez suivre le lien donné pour l'activer."
    ]);
  }

  public function validation()
  {
    $verification_string = explode("/", $_SERVER["REQUEST_URI"])[2];

    if (!$verification_string)
      return $this->redirect("/accueil", "home");

    if (!Application::$instance->database->activateAccount($verification_string))
      return $this->redirect("/accueil", "home");
    return $this->render("registration/verification");
  }
}
