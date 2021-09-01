<?php

namespace app\admin;

use app\core\Request;
use app\models\Login;
use app\core\Controller;

class AdminController extends Controller
{
  public function login(Request $request)
  {
    if ($this->isLoggedAsUser())
      return $this->redirectNotFound();

    $form = Login::getForm("/admin-connexion");

    if ($request->isGet())
      return $this->render("admin/login", [
        "form" => $form->createView()
      ]);

    $login = new Login($request->getBody());
    $validation = $login->validateAdmin();

    if ($validation === Login::ERROR_NOT_ADMIN)
      return $this->redirect("/accueil", "home", [
        "error_message" => $validation
      ]);

    if ($validation !== 1)
      return $this->render("admin/login", [
        "form" => $form->createView(),
        "error_message" => $validation
      ]);

    $login->setLoggedUser();
    return $this->render("admin/panel", []);
  }
}
