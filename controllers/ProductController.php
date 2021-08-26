<?php

namespace app\controllers;

use app\core\Request;
use app\models\Product;
use app\core\Controller;
use app\core\Application;

class ProductController extends Controller
{
  private function canBeAddedToBasket(array $product): bool
  {
    if (!$this->hasSessionUser())
      return false;

    $user_id = (int)$this->getSessionUser()["id"];
    if ((int)$product["seller_id"] === $user_id)
      return false;

    return !(bool)Application::$instance
      ->database
      ->findOne(
        "basket",
        ["*"],
        [
          "buyer_id" => $user_id,
          "product_id" => (int)$product["id"]
        ]
      );
  }

  public function product(Request $request)
  {
    $id = $request->getParamId();
    if (!$id)
      return $this->redirectNotFound();

    $product = $this->findProductById($id);
    if (!$product)
      return $this->redirectNotFound();

    return $this->render("products/single", [
      "product" => $product,
      "is_addable_to_basket" => $this->canBeAddedToBasket($product)
    ]);
  }

  public function add(Request $request)
  {
    $user = $this->getSessionUser();
    if (!$user)
      return $this->redirectToLogin();

    $form = $this->getAddForm();

    if ($request->isGet())
      return $this->render("products/add", [
        "form" => $form->createView(),
      ]);

    $product = new Product($_POST, $_FILES, (int)$user["id"]);
    $validation = $product->validate();

    if ($validation !== 1)
      return $this->render("products/add", [
        "form" => $form->createView(),
        "error_message" => $validation
      ]);

    $product->save();
    Application::$instance->session->updateProducts();
    return $this->redirect("/mes-articles", "user/my-products", [
      "user" => $user
    ]);
  }

  public function update(Request $request)
  {
    $user = $this->getSessionUser();
    if (!$user) return $this->redirectToLogin();

    $id = $request->getParamId();
    if (!$id) return $this->redirectNotFound();

    $user_id = (int)$user["id"];
    $product = $this->findProductById($id);
    if (!$product) return $this->redirectNotFound();
    $is_user_product = (int)$product["seller_id"] === $user_id;

    if (!$this->isLoggedAsAdmin() && !$is_user_product)
      return $this->redirectNotFound();

    $form = $this->getUpdateForm("/modifier-article/$id", $product);
    $params = [
      "form" => $form->createView(),
      "product_name" => $product["name"],
      "product_image" => $product["image"]
    ];

    if ($request->isGet())
      return $this->render("products/update", $params);

    $updated_product = new Product($_POST, $_FILES, $user_id);
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
    return $this->redirect("/mes-articles", "user/my-products", [
      "user" => $user
    ]);
  }

  public function delete(Request $request)
  {
    if (!$request->isPost()) return $this->redirectNotFound();

    $user = $this->getSessionUser();
    if (!$user) return $this->redirectToLogin();

    $id = $request->getParamId();
    if (!$id) return $this->redirectNotFound();

    $product = $this->findProductById($id);
    if (!$product) return $this->redirectNotFound();

    $user_id = (int)$user["id"];
    $is_user_product = (int)$product["seller_id"] === $user_id;
    if (!$is_user_product)
      return $this->redirectNotFound();

    $product_id = (int)$product["id"];
    $deletion = Application::$instance
      ->database
      ->deleteProduct($product_id);
    Application::$instance->session->updateProducts();

    if (!$deletion)
      return $this->render("user/my-basket", [
        "error_message" => "La suppression du produit a échoué."
      ]);

    return $this->render("user/my-basket");
  }
}
