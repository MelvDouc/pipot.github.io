<?php

namespace app\admin;

use app\core\Request;
use app\models\User;

class UserController extends AdminController
{
  public function update(Request $request)
  {
    if (!$this->isLoggedAsAdmin())
      return $this->redirectHome();

    if (!($id = $request->getParamId()))
      return $this->redirectNotFound();

    if (!($user = User::findOne(["id" => $id])))
      return $this->redirectNotFound();

    if ($request->isGet())
      return $this->render("admin/update-user", [
        "title" => "Modifier un utilisateur",
        "user" => $user
      ]);
  }
}
