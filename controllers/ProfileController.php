<?php

namespace app\controllers;

use app\models\User;
use app\models\Product;
use app\core\Controller;
use app\core\Application;

class ProfileController extends Controller
{
  private function isUserProfile($dbId)
  {
    if (!Application::$instance->session->hasUser())
      return false;
    return (bool)($dbId == Application::$instance->session->getUser()["id"]);
  }

  public function profile()
  {
    $id = $this->getParamId();
    if (!$id)
      return $this->redirectNotFound;

    $user = Application::$instance
      ->database
      ->findOne(User::DB_TABLE, ["id" => $id]);
    if (!$user)
      return $this->redirectNotFound();

    return $this->render("user/profile", [
      "user" => $user,
      "isUserProfile" => $this->isUserProfile($user["id"])
    ]);
  }

  public function my_profile()
  {
    if (!Application::$instance->session->hasUser())
      return $this->redirect("connexion", "authentication/login", [
        "error" => "Vous n'êtes pas connecté."
      ]);

    $user = Application::$instance->session->getUser();

    return $this->render("user/my-profile", [
      "user" => $user
    ]);
  }

  public function my_products()
  {
    if (!Application::$instance->session->hasUser())
      return $this->redirect("connexion", "authentication/login", [
        "error" => "Vous n'êtes pas connecté."
      ]);

    $user = Application::$instance->session->getUser();
    $user["products"] = Application::$instance->database->findAll(
      Product::DB_TABLE,
      ["*"],
      ["seller_id" => $user["id"]]
    );

    return $this->render("user/my-products", [
      "user" => $user,
    ]);
  }
}