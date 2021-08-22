<?php

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\models\Product;

class ProductController extends Controller
{
  public function product()
  {
    $id = $this->getParamId();

    if (!$id)
      return $this->redirectNotFound();

    $product = Application::$instance->database->findOne(Product::DB_TABLE, "id", $id);
    if (!$product)
      return $this->redirectNotFound();

    return $this->render("products/single", [
      "product" => $product
    ]);
  }
}