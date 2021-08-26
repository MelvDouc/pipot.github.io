<?php

namespace app\admin;

use app\core\Request;
use app\models\Product;
use app\core\Application;

class ProductController extends AdminController
{
  public function update(Request $request)
  {
    if (!$this->isLoggedAsAdmin())
      return $this->redirectHome();

    $id = $request->getParamId();
    if (!$id) return $this->redirectNotFound();

    $product = $this->findProductById($id);
    if (!$product) return $this->redirectNotFound();

    $form = $this->getUpdateForm("/admin-modifier-article/$id", $product);
    $params = [
      "form" => $form->createView(),
      "product_name" => $product["name"],
      "product_image" => $product["image"],
      "is_admin" => true
    ];

    if ($request->isGet())
      return $this->render("products/update", $params);

    $updated_product = new Product($_POST, $_FILES, (int)$product["seller_id"]);
    $validation = $updated_product->validate();

    if ($validation !== 1) {
      $params["error_message"] = $validation;
      return $this->render("products/update", $params);
    }

    if (!$updated_product->updateFrom($product)) {
      $params["error_message"] = "La modification de l'article a échoué.";
      return $this->render("products/update", $params);
    }

    Application::$instance->session->updateProducts();
    return $this->redirect("/admin-articles", "admin/all-products", []);
  }

  public function delete(Request $request)
  {
    if (!$request->isPost())
      return $this->redirectNotFound();

    if (!$this->isLoggedAsAdmin())
      return $this->redirectHome();

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
      return $this->redirect("/admin-articles", "admin/all-products", [
        "error_message" => "La suppression du produit a échoué."
      ]);

    return $this->redirect("/admin-articles", "admin/all-products");
  }
}
