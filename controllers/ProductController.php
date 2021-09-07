<?php

namespace app\controllers;

use app\core\Request;
use app\models\Product;
use app\core\Controller;
use app\core\Application;
use app\models\forms\products\AddProductForm;
use app\models\forms\products\UpdateProductForm;

class ProductController extends Controller
{
  private function canBeAddedToBasket(array $product): bool
  {
    if (!($user = $this->getSessionUser()))
      return false;

    $user_id = (int)$user["id"];
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

    $form = new AddProductForm();

    if ($request->isGet())
      return $this->render("products/add", [
        "form" => $form->createView(),
      ]);


    $product = new Product($request->getBody(), (int)$user["id"]);
    $validation = $product->validate();

    if ($validation !== 1)
      return $this->render("products/add", [
        "form" => $form->createView(),
        "error_message" => $validation
      ]);

    if (!$product->save())
      return $this->render("products/add");
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

    $form = new UpdateProductForm("/modifier-article/$id", $product);
    $params = [
      "form" => $form->createView(),
      "product_name" => $product["name"],
      "product_image" => $product["image"]
    ];

    if ($request->isGet())
      return $this->render("products/update", $params);

    $updated_product = new Product($request->getBody(), $user_id);
    $validation = $updated_product->validate();

    if ($validation !== 1) {
      $params["error_message"] = $validation;
      return $this->render("products/update", $params);
    }

    if (!$updated_product->updateFrom($product)) {
      $params["error_message"] = "La modification de l'article a échoué.";
      return $this->render("products/update", $params);
    }

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

    if (!$deletion)
      return $this->render("user/my-products", [
        "error_message" => "La suppression du produit a échoué."
      ]);

    return $this->render("user/my-products");
  }

  public function search(Request $request)
  {
    if (!$request->isPost())
      return $this->redirectNotFound();

    if (!($keywords = $_POST["keywords"] ?? null))
      return $this->redirectNotFound();

    $products = Application::$instance
      ->database
      ->findProductByKeywords($keywords);

    return $this->render("products/search", [
      "search" => $keywords,
      "products" => $products ?? false
    ]);
  }
}
