<?php

namespace app\admin;

use app\core\Application;
use app\core\Request;
use app\models\Product;
use app\models\User;

class PanelController extends AdminController
{
  public function panel(Request $request)
  {
    if (!$this->isLoggedAsAdmin())
      return $this->login($request);

    return $this->render("admin/panel", []);
  }

  public function all_products(Request $request)
  {
    if (!$this->isLoggedAsAdmin())
      return $this->login($request);

    $products = Product::findAll();

    return $this->render("admin/all-products", [
      "title" => "Liste des articles",
      "products" => $products,
      "flashSuccess" => $this->getFlash("success")
    ]);
  }

  public function all_users(Request $request)
  {
    if (!$this->isLoggedAsAdmin())
      return $this->login($request);

    return $this->render("admin/all-users", [
      "title" => "Liste des utilisateurs",
      "users" => User::findAll()
    ]);
  }
}
