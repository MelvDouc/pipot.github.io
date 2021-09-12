<?php

namespace app\controllers;

use app\core\Controller;
use app\core\Application;
use app\core\Request;
use app\models\User;

class RegisterController extends Controller
{
  public function register(Request $request)
  {
    if (Application::$instance->session->hasUser())
      return $this->redirectHome();

    if ($request->isGet())
      return $this->render("registration/index", [
        "title" => "Inscription"
      ]);

    if (!isset($_POST["agree-terms"]))
      return $this->render("registration/index", [
        "title" => "Inscription",
        "flashErrors" => ["Veuillez accepter les conditions d'utilisation."]
      ]);

    $user = new User();
    $user->username = $_POST["username"] ?? null;
    $user->email = $_POST["email"] ?? null;
    $user->setPasswords("plain", $_POST["password"] ?? null);
    $user->setPasswords("confirm", $_POST["confirm-password"] ?? null);

    if (!$user->isValid())
      return $this->render("register/index", [
        "title" => "Inscription",
        "flashErrors" => $user->getErrors()
      ]);

    $user->save();
    $user->sendConfirmation();
    return $this->redirect("/accueil", "home/home");
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
