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
