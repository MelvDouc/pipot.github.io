<?php

namespace app\controllers;

use app\core\Controller;
use app\core\Application;
use app\core\Request;
use app\models\FormGroup;
use app\models\User;

class RegisterController extends Controller
{
  public function register(Request $request)
  {
    if (Application::$instance->session->hasUser())
      return $this->redirect("/accueil");

    $formGroups = [
      new FormGroup("Nom d'utilisateur", "username", "text", 25),
      new FormGroup("Adresse email", "email", "email"),
      new FormGroup("Mot de passe", "password", "password", 25),
      new FormGroup("Confirmer mot de passe", "confirm_password", "password", 25)
    ];

    if ($request->isPost())
    {
      $user = new User($_POST);
      $validation = $user->validate();

      if ($validation !== 1)
        return $this->render("registration/index", [
          "formGroups" => $formGroups,
          "error" => $validation
        ]);

      $user->save();
      $user->send_verification();
      
      return $this->render("home", [
        "flash_message" => "Nous vous avons envoyé un mail confirmant la création de votre compte. Veuillez suivre le lien donné pour l'activer."
      ]);
    }

    $this->render("registration/index", [
      "formGroups" => $formGroups
    ]);
  }

  public function validation()
  {
    $verification_string = explode("/", $_SERVER["REQUEST_URI"])[2];
    if (!$verification_string)
      return $this->redirect("/accueil");
    if (!Application::$instance->database->activateAccount($verification_string))
      return $this->redirect("/accueil");
    return $this->render("registration/verification");
  }
}