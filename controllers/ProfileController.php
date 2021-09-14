<?php

namespace app\controllers;

use app\models\User;
use app\core\Controller;
use app\core\Application;
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

  public function profile(Request $request)
  {
    if (!($id = $request->getParamId()))
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

  public function updatePassword(Request $request)
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    if ($request->isGet())
      return $this->render("user/update-password", [
        "title" => "Modifier mon mot de passe"
      ]);

    $body = $request->getBody();
    $user->setPasswords("old", $body["old-password"] ?? null);
    $user->setPasswords("new", $body["new-password"] ?? null);
    $user->setPasswords("confirm", $body["confirm-new-password"] ?? null);

    if (!$user->isPasswordUpdateValid())
      return $this->render("user/update-password", [
        "title" => "Modifier mon mot de passe",
        "flashErrors" => $user->getErrors()
      ]);

    $user->updatePassword();
    $this->setFlash("success", "Le mot de passe a bien été mis à jour.");
    return $this->redirect("/mon-profil");
  }

  public function updateContact(Request $request)
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    if ($request->isGet())
      return $this->render("user/update-contact", [
        "title" => "Modifier mes coordonnées",
        "user" => $user
      ]);

    $body = $request->getBody();
    $contactProperties = ["first_name", "last_name", "postal_address", "city", "zip_code", "phone_number"];
    foreach ($contactProperties as $property)
      $user->{$property} = $body[$property] ?? null;

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
