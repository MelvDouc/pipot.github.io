<?php

namespace app\controllers;

use app\models\Form;
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
        "cart",
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

    $product = Application::$instance->database->findProductById($id);
    if (!$product)
      return $this->redirectNotFound();

    return $this->render("products/single", [
      "product" => $product,
      "is_addable_to_basket" => $this->canBeAddedToBasket($product)
    ]);
  }

  public function add(Request $request)
  {
    if (!$this->hasSessionUser())
      return $this->redirectToLogin();

    $user = Application::$instance->session->getUser();
    $form = new Form();
    $form->start("/ajouter-produit", true, "add-product-form");
    $form->add_input("Nom", "name", "text", true, ["maxlength" => 50]);
    $form->add_textarea("Description", "description", true);
    $form->add_input("Prix en Pipots", "price", "number", true);
    $form->add_input("Quantité", "quantity", "number", true, ["value" => 1]);
    $form->add_input("Image", "image", "file", false);
    $form->add_submit("Ajouter");
    $form->end();

    $categories = Application::$instance
      ->database
      ->findAll("categories", ["id", "name"]);

    if ($request->isGet())
      return $this->render("products/add", [
        "form" => $form->createView(),
        "categories" => $categories
      ]);

    $product = new Product($_POST, $_FILES, $user["id"]);
    $validation = $product->validate();

    if ($validation !== 1)
      return $this->render("products/add", [
        "form" => $form->createView(),
        "categories" => $categories,
        "error" => $validation
      ]);

    $product->save();
    Application::$instance->session->updateProducts();
    return $this->redirect("/mes-articles", "user/my-products", [
      "user" => $user
    ]);
  }
}
