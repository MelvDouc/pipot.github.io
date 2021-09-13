<?php

namespace app\admin;

use app\core\Request;
use app\core\Controller;

class AdminController extends Controller
{
  public function login(Request $request)
  {
    if ($this->isLoggedAsUser())
      return $this->redirectNotFound();

    if ($request->isGet())
      return $this->render("admin/login");
  }
}
