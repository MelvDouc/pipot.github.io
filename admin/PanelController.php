<?php

namespace app\admin;

use app\core\Application;
use app\core\Request;
use app\models\Product;

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

    $products = Application::$instance
      ->database
      ->findAllProducts();

    return $this->render("admin/all-products", [
      "products" => $products
    ]);
  }
}
