<?php

namespace app\controllers;

use app\models\Form;
use app\core\Request;
use app\models\Product;
use app\core\Controller;
use app\core\Application;
use app\models\Basket;

class ProductController extends Controller
{
  private function is_addable_to_basket($product_id, $seller_id)
  {
    if (!$this->hasSessionUser())
      return false;
    
    $user_id = (int)$this->getSessionUser()["id"];
    if ((int)$seller_id === $user_id)
      return false;

    $basket = new Basket($user_id);
    return !$basket->has((int)$product_id);
  }

  public function product()
  {
    $id = $this->getParamId();
    if (!$id)
      return $this->redirectNotFound();

    $product = Application::$instance->database->findProductById($id);
    if (!$product)
      return $this->redirectNotFound();

    return $this->render("products/single", [
      "product" => $product,
      "is_addable_to_basket" => $this->is_addable_to_basket($product["id"], $product["seller_id"])
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

    if ($request->isPost()) {
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
      return $this->redirect("mes-articles", "user/my-products", [
        "user" => $user
      ]);
    }

    return $this->render("products/add", [
      "form" => $form->createView(),
      "categories" => $categories
    ]);
  }
}