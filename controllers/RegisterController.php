<?php

namespace app\controllers;

use app\core\Controller;
use app\core\Application;
use app\core\Request;
use app\models\forms\RegisterForm;
use app\models\User;

class RegisterController extends Controller
{
  public function register(Request $request)
  {
    if (Application::$instance->session->hasUser())
      return $this->redirectHome();

    $form = new RegisterForm();

    if ($request->isGet())
      return $this->render("registration/index", [
        "form" => $form->createView()
      ]);

    $user = new User($request->getBody());
    $validation = $user->validate();

    if ($validation !== 1)
      return $this->render("registration/index", [
        "form" => $form->createView(),
        "error_message" => $validation
      ]);

    $user->save();
    $user->send_verification();

    return $this->redirectHome([
      "success_message" => "Nous vous avons envoyé un mail confirmant la création de votre compte. Veuillez suivre le lien donné pour l'activer."
    ]);
  }

  public function validation()
  {
    $verification_string = explode("/", $_SERVER["REQUEST_URI"])[2];

    if (!$verification_string)
      return $this->redirectHome();

    if (!Application::$instance->database->activateAccount($verification_string))
      return $this->redirectHome();
    return $this->render("registration/verification");
  }
}
