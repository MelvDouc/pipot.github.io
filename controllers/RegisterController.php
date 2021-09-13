<?php

namespace app\controllers;

use app\core\Controller;
use app\core\Request;
use app\models\User;

class RegisterController extends Controller
{
  public function register(Request $request)
  {
    if ($this->getSessionUser())
      return $this->redirect("/mon-profil");

    if ($request->isGet())
      return $this->render("registration/index", [
        "title" => "Inscription"
      ]);

    if (!isset($_POST["agree-terms"]))
      return $this->render("registration/index", [
        "title" => "Inscription",
        "flashErrors" => ["Veuillez accepter les conditions d'utilisation."]
      ]);

    $body = $request->getBody();
    $user = new User();
    $user->username = $body["username"] ?? null;
    $user->email = $body["email"] ?? null;
    $user->setPasswords("plain", $body["password"] ?? null);
    $user->setPasswords("confirm", $body["confirm-password"] ?? null);

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

    $user = User::findOne(["verification_string" => $verification_string]);
    $user->activateAcouunt();
    return $this->render("registration/verification");
  }
}
