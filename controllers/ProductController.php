<?php

namespace app\controllers;

use app\core\Request;
use app\models\Product;
use app\core\Controller;
use app\core\Application;
use app\models\Category;

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
      "title" => $product->name,
      "product" => $product,
      "addableToBasket" => $this->canBeAddedToBasket($product)
    ]);
  }

  public function add(Request $request)
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    $categories = Category::findAll();

    if ($request->isGet())
      return $this->render("products/add", [
        "title" => "Ajouter un article",
        "categories" => $categories
      ]);

    $body = $request->getBody();
    $product = new Product();
    $product->name = $body["name"] ?? null;
    $product->description = $body["description"] ?? null;
    $product->price = (int) $body["price"] ?? null;
    $product->quantity = (int) $body["quantity"] ?? null;
    $product->category_id = (int) $body["category_id"] ?? null;
    $product->seller_id = $user->id;
    if (isset($_FILES["image"]) && $_FILES["image"]["name"])
      $product->setFile($_FILES["image"] ?? null);

    if (!$product->isValid())
      return $this->render("products/add", [
        "title" => "Ajouter un article",
        "categories" => $categories,
        "flashErrors" => $product->getErrors()
      ]);

    $product->save();
    return $this->redirect("/mes-articles");
  }

  public function update(Request $request)
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    if (!($id = $request->getParamId()))
      return $this->redirectNotFound();

    if (!($product = Product::findOne(["id" => $id])))
      return $this->redirectNotFound();

    $categories = Category::findAll();

    if ($request->isGet())
      return $this->render("products/update", [
        "title" => "Modifier un article",
        "categories" => $categories,
        "product" => $product
      ]);

    $body = $request->getBody();
    $product->name = $body["name"] ?? null;
    $product->description = $body["description"] ?? null;
    $product->price = (int) $body["price"] ?? null;
    $product->quantity = (int) $body["quantity"] ?? null;
    $product->category_id = (int) $body["category_id"] ?? null;
    $product->seller_id = $user->id;
    $product->setFile($_FILES["image"] ?? null);

    if (!$product->isValid())
      return $this->render("products/update", [
        "title" => "Modifier un article",
        "categories" => $categories,
        "flashErrors" => $product->getErrors()
      ]);

    $product->update();
    return $this->redirect("/mes-articles");
  }

  public function delete(Request $request)
  {
    if (!$this->getSessionUser())
      return $this->redirectToLogin();

    if (!($id = $request->getParamId()))
      return $this->redirectNotFound();

    if (!($product = Product::findOne(["id" => $id])))
      return $this->redirectNotFound();

    $product->delete();
    return $this->redirect("/mes-articles");
  }

  public function search(Request $request)
  {
    $body = $request->getBody();
    $keywords = $body["keywords"] ?? null;

    if (!$keywords) return $this->redirectHome();

    $dbProducts = Application::$instance
      ->database
      ->findProductByKeywords($keywords);
    $products = array_map(fn ($dbProd) => Product::instantiate($dbProd), $dbProducts);

    return $this->render("/products/search", [
      "title" => "Résultats de la recherche",
      "products" => $products,
      "search" => $keywords
    ]);
  }
}
