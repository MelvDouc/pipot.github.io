<?php

namespace app\controllers;

use app\models\User;
use app\core\Controller;
use app\core\Application;
use app\core\Request;
use app\models\forms\Form;
use app\models\forms\updateContactForm;
use app\models\forms\UpdatePasswordForm;

class ProfileController extends Controller
{
  private function isUserProfile(int $param_id): bool
  {
    if (!$this->hasSessionUser())
      return false;
    $sessionUser = $this->getSessionUser();
    $sessionId = (int)$sessionUser["id"];
    return $param_id === $sessionId;
  }

  private function getParamUser(int $param_id): array | false
  {
    return Application::$instance
      ->database
      ->findOne(User::DB_TABLE, ["*"], ["id" => $param_id]);
  }

  public function profile(Request $request)
  {
    $id = $request->getParamId();
    if (!$id)
      return $this->redirectNotFound();

    $user = $this->getParamUser($id);
    if (!$user)
      return $this->redirectNotFound();

    return $this->render("user/profile", [
      "user" => $user,
      "isUserProfile" => $this->isUserProfile($id)
    ]);
  }

  public function my_profile()
  {
    if (!$this->hasSessionUser())
      return $this->redirectToLogin();

    $user = $this->getSessionUser();

    return $this->render("user/profile", [
      "user" => $user,
      "isUserProfile" => true
    ]);
  }

  public function updatePassword(Request $request)
  {
    if (!$this->hasSessionUser())
      return $this->redirectToLogin();

    $user = $this->getSessionUser();
    $id = (int)$user["id"];
    $form = new UpdatePasswordForm($user);

    if ($request->isGet())
      return $this->render("user/update-password", [
        "form" => $form->createView()
      ]);

    $form->setBody($request->getBody());
    $validation = $form->validate();

    if ($validation !== 1)
      return $this->render("user/update-password", [
        "form" => $form->createView(),
        "error_message" => $validation
      ]);

    if (!Application::$instance
      ->database
      ->updatePassword($id, $form->getHashedPassword()))
      return $this->render("/user-update-password", [
        "form" => $form->createView(),
        "error_message" => "Une erreur s'est produite lors de la mise à jour du mot de passe."
      ]);

    Application::$instance->session->updateUser();
    return $this->redirect("/mon-profil", "user/profile", [
      "success_message" => "Votre mot de passe a bien été modifié."
    ]);
  }

  public function updateContact(Request $request)
  {
    if (!$this->hasSessionUser())
      return $this->redirectToLogin();

    $user = $this->getSessionUser();
    $id = (int)$user["id"];
    $form = new updateContactForm($this->getSessionUser());

    if ($request->isGet())
      return $this->render("user/update-contact", [
        "form" => $form->createView()
      ]);

    $form->setBody($request->getBody());
    $validation = $form->validate();

    if ($validation !== 1)
      return $this->render("user/update-contact", [
        "form" => $form->createView(),
        "error_message" => $validation
      ]);

    if (!Application::$instance
      ->database
      ->updateContact($id, $form->getBody()))
      return $this->render("user/update-contact", [
        "form" => $form->createView(),
        "error-message" => "Une erreur s'est produite lors de la mise à jour des coordonnées."
      ]);
    Application::$instance
      ->session
      ->updateUser();
    return $this->redirect("/mon-profil", "user/profile");
  }

  public function my_products()
  {
    if (!$this->hasSessionUser())
      return $this->redirectToLogin();

    Application::$instance->session->updateProducts();
    $user = $this->getSessionUser();

    return $this->render("user/my-products", [
      "user" => $user,
    ]);
  }
}
