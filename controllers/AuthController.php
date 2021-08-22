<?php

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\Request;
use app\models\FormGroup;
use app\models\User;

class AuthController extends Controller
{
  public function login(Request $request)
  {
    if (Application::$instance->session->hasUser())
      return $this->render("home", [
        "flash_message" => "Vous êtes déjà connecté."
      ]);

    $formGroups = [
      new FormGroup("Nom d'utilisateur ou adresse email", "uuid", "text"),
      new FormGroup("Mot de passe", "password", "password"),
    ];

    if ($request->isPost()) {
      extract($_POST);
      $error = null;

      if (!$uuid || !$password)
        $error = "Veuillez remplir tous les champs.";
      else {
        $user = Application::$instance->database->findOne(
          User::DB_TABLE,
          [
            "username" => $uuid,
            "email" => $uuid,
          ],
          "OR"
        );
        if (!$user)
          $error = "Utilisateur non trouvé.";
        else if (!Application::$instance->database->isCorrectPassword($uuid, $password))
          $error = "Mot de passe incorrect.";
        else if ($user["is_account_active"] == 0)
          $error = "Vous n'avez pas encore activé votre compte.";
      }

      if ($error)
        return $this->render("authentication/login", [
          "formGroups" => $formGroups,
          "error" => $error
        ]);

      Application::$instance->session->setUser($user);
      header("Location: mon-profil");
      return $this->render("user/my-profile", ["user" => $user]);
    }

    return $this->render("authentication/login", [
      "formGroups" => $formGroups
    ]);
  }

  public function logout()
  {
    Application::$instance->session->removeUser();
    header("Location: /");
  }

  public function my_profile()
  {
    if (!Application::$instance->session->hasUser())
      return $this->render("authentication/login", ["error" => "Vous n'êtes pas connecté."]);

    $user = Application::$instance->session->getUser();

    return $this->render("user/my-profile", [
      "user" => $user
    ]);
  }

  public function profile()
  {
    $id = $this->getParamId();
    if (!$id)
      return $this->render("404");

    $user = Application::$instance->database->findOne(User::DB_TABLE, ["id" => $id]);
    if (!$user)
      return $this->render("404");

    return $this->render("user/profile", [
      "user" => $user,
      "isUserProfile" => $this->isUserProfile($user["id"])
    ]);
  }

  public function my_products()
  {
    if (!Application::$instance->session->hasUser())
      return $this->render("authentication/login", ["error" => "Vous n'êtes pas connecté."]);

    $user = Application::$instance->session->getUser();
    $products = Application::$instance
      ->database
      ->findAll("products", ["seller_id" => $user["id"]]);

    return $this->render("user/my-products", [
      "user" => $user,
      "products" => $products
    ]);
  }

  private function isUserProfile($dbId)
  {
    if (!Application::$instance->session->hasUser())
      return false;
    return (bool)($dbId == Application::$instance->session->getUser()["id"]);
  }
}
