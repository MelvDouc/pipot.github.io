<?php

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\Request;
use app\models\FormGroup;
use app\models\Product;

class ProductController extends Controller
{
  public function product()
  {
    $id = $this->getParamId();

    if (!$id)
      return $this->redirectNotFound();

    $product = Application::$instance->database->findOne(Product::DB_TABLE, ["*"], ["id" => $id]);
    if (!$product)
      return $this->redirectNotFound();

    return $this->render("products/single", [
      "product" => $product
    ]);
  }

  public function add(Request $request)
  {
    if (!Application::$instance->session->hasUser())
      return $this->render("authentication/login", ["error" => "Vous n'êtes pas connecté."]);
    
    $user = Application::$instance->session->getUser();
    $formGroups = [
      new FormGroup("Nom", "name", "text", 50, "true"),
      new FormGroup("Description", "description", "textarea", null, "true"),
      new FormGroup("Prix en Pipots", "price", "number", null, "true"),
      new FormGroup("Image", "image", "file", null, false)
    ];
    $categories = Application::$instance
      ->database
      ->findAll("categories", ["id", "name"]);

    if ($request->isPost()) {
      $product = new Product($_POST, $_FILES, $user["id"]);
      $validation = $product->validate();
      
      if ($validation !== 1)
        return $this->render("products/add", [
          "formGroups" => $formGroups,
          "categories" => $categories,
          "error" => $validation
        ]);

      $product->save();
      // Application::vardump($product);
      // return;
      Application::$instance->session->updateProducts();
      return $this->redirect("mes-articles", "user/my-products", [
        "user" => $user
      ]);
    }

    return $this->render("products/add", [
      "formGroups" => $formGroups,
      "categories" => $categories
    ]);
  }
}