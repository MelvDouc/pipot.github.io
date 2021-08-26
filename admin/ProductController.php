<?php

namespace app\admin;

use app\core\Application;
use app\core\Request;

class ProductController extends AdminController
{
  public function delete(Request $request)
  {
    if (!$request->isPost())
      return $this->redirectNotFound();

    if (!$this->isLoggedAsAdmin())
      return $this->redirect("", "home");

    $id = $request->getParamId();
    if (!$id) return $this->redirectNotFound();

    $product = $this->findProductById($id);
    if (!$product) return $this->redirectNotFound();

    $product_id = (int)$product["id"];
    $deletion = Application::$instance
      ->database
      ->deleteProduct($product_id);
    Application::$instance->session->updateProducts();

    if (!$deletion)
      return $this->redirect("admin-articles", "admin/all-products", [
        "error_message" => "La suppression du produit a échoué."
      ]);

    return $this->redirect("admin-articles", "admin/all-products");
  }
}
