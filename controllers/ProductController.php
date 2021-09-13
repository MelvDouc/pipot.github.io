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
  private function canBeAddedToBasket(Product $product): bool
  {
    if (!($user = $this->getSessionUser()))
      return false;

    if ($product->seller_id === $user->id)
      return false;

    return !(bool)Application::$instance
      ->database
      ->findOne(
        "basket",
        ["*"],
        [
          "buyer_id" => $user->id,
          "product_id" => $product->id
        ]
      );
  }

  public function product(Request $request)
  {
    if (!($id = $request->getParamId()))
      return $this->redirectNotFound();

    if (!($product = Product::findOne(["id" => $id])))
      return $this->redirectNotFound();

    return $this->render("products/single", [
      "product" => $product,
      "addableToBasket" => $this->canBeAddedToBasket($product)
    ]);
  }
}
