<?php

namespace app\admin;

use app\core\Request;
use app\models\forms\users\UpdateUserForm;

class UserController extends AdminController
{
  public function update(Request $request)
  {
    if (!$this->isLoggedAsAdmin())
      return $this->redirectHome();

    $id = $request->getParamId();
    if (!$id) return $this->redirectNotFound();

    $user = $this->findUserById($id);
    if (!$user) return $this->redirectNotFound();

    $form = new UpdateUserForm("/admin-modifier-utilisateur/$id", $user);
    $params = [
      "form" => $form->createView(),
      "user" => $user
    ];

    if ($request->isGet())
      return $this->render("admin/update-user", $params);
  }
}
