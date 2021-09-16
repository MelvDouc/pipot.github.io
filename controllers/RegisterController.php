<?php

namespace app\controllers;

use app\core\Controller;
use app\core\Request;
use app\models\User;

class RegisterController extends Controller
{
  public function register(Request $req)
  {
    if ($this->getSessionUser())
      return $this->redirect("/mon-profil");

    if ($req->isGet())
      return $this->render("registration/index", [
        "title" => "Inscription"
      ]);

    if (!isset($_POST["agree-terms"]))
      return $this->render("registration/index", [
        "title" => "Inscription",
        "flashErrors" => ["Veuillez accepter les conditions d'utilisation."]
      ]);

    $user = new User();
    $user->username = $req->get("username");
    $user->email = $req->get("email");
    $user->setPasswords("plain", $req->get("password"));
    $user->setPasswords("confirm", $req->get("confirm-password"));

    if (!$user->isValid())
      return $this->render("register/index", [
        "title" => "Inscription",
        "flashErrors" => $user->getErrors()
      ]);

    $user->save();
    $user->sendConfirmation();
    return $this->redirectHome();
  }

  public function validation()
  {
    $verification_string = explode("/", $_SERVER["REQUEST_URI"])[2];

    if (!$verification_string)
      return $this->redirectHome();

    if (!($user = User::findOne(["verification_string" => $verification_string])))
      return $this->redirectNotFound();

    $user->activateAcouunt();
    return $this->render("registration/verification");
  }
}
