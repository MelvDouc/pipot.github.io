<?php

namespace app\controllers;

use app\models\User;
use app\core\Controller;
use app\core\Request;

class ProfileController extends Controller
{
  private function isUserProfile(int $param_id): bool
  {
    if (!($user = $this->getSessionUser()))
      return false;
    $sessionId = $user->id;
    return $param_id === $sessionId;
  }

  public function profile(Request $req)
  {
    if (!($id = $req->getParamId()))
      return $this->redirectNotFound();

    $user = User::findOne(["id" => $id]);
    return $this->render("user/profile", [
      "title" => "Profil de $user->username",
      "user" => $user,
      "isUserProfile" => $this->isUserProfile($id)
    ]);
  }

  public function myProfile()
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    return $this->render("user/profile", [
      "title" => "Mon profil",
      "user" => $user,
      "isUserProfile" => true,
      "flashSuccess" => $this->getFlash("success")
    ]);
  }

  public function myProducts()
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    return $this->render("user/my-products", [
      "title" => "Mes articles",
      "user" => $user,
      "flashSuccess" => $this->getFlash("success"),
      "flashErrors" => $this->getFlash("errors")
    ]);
  }

  public function updatePassword(Request $req)
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    if ($req->isGet())
      return $this->render("user/update-password", [
        "title" => "Modifier mon mot de passe"
      ]);

    $user->setPasswords("old", $req->get("old-password"));
    $user->setPasswords("new", $req->get("new-password"));
    $user->setPasswords("confirm", $req->get("confirm-new-password"));

    if (!$user->isPasswordUpdateValid())
      return $this->render("user/update-password", [
        "title" => "Modifier mon mot de passe",
        "flashErrors" => $user->getErrors()
      ]);

    $user->updatePassword();
    $this->setFlash("success", "Le mot de passe a bien été mis à jour.");
    return $this->redirect("/mon-profil");
  }

  public function updateContact(Request $req)
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    if ($req->isGet())
      return $this->render("user/update-contact", [
        "title" => "Modifier mes coordonnées",
        "user" => $user
      ]);

    $contactProperties = ["first_name", "last_name", "postal_address", "city", "zip_code", "phone_number"];
    foreach ($contactProperties as $property)
      $user->{$property} = $req->get($property);

    if (!$user->isContactUpdateValid())
      return $this->render("user/update-contact", [
        "title" => "Modifier mes coordonnées",
        "user" => $user,
        "flashErrors" => $user->getErrors()
      ]);

    $user->updateContact();
    $this->setFlash("success", "Les coordonnées ont bien été mises à jour.");
    return $this->redirect("/mon-profil");
  }
}
