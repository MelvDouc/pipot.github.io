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

  public function product(Request $req)
  {
    if (!($id = $req->getParamId()))
      return $this->redirectNotFound();

    if (!($product = Product::findOne(["id" => $id])))
      return $this->redirectNotFound();

    return $this->render("products/single", [
      "title" => $product->name,
      "product" => $product,
      "addableToBasket" => $this->canBeAddedToBasket($product)
    ]);
  }

  public function add(Request $req)
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    $categories = Category::findAll();

    if ($req->isGet())
      return $this->render("products/add", [
        "title" => "Ajouter un article",
        "categories" => $categories
      ]);

    $product = new Product();
    $product->name = $req->get("name");
    $product->description = $req->get("description");
    $product->price = (int) $req->get("price");
    $product->quantity = (int) $req->get("quantity");
    $product->category_id = (int) $req->get("category_id");
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

  public function update(Request $req)
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    if (!($id = $req->getParamId()))
      return $this->redirectNotFound();

    if (!($product = Product::findOne(["id" => $id])))
      return $this->redirectNotFound();

    $categories = Category::findAll();

    if ($req->isGet())
      return $this->render("products/update", [
        "title" => "Modifier un article",
        "categories" => $categories,
        "product" => $product
      ]);

    $product->name = $req->get("name");
    $product->description = $req->get("description");
    $product->price = (int) $req->get("price");
    $product->quantity = (int) $req->get("quantity");
    $product->category_id = (int) $req->get("category_id");
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

  public function delete(Request $req)
  {
    if (!$this->getSessionUser())
      return $this->redirectToLogin();

    if (!($id = $req->getParamId()))
      return $this->redirectNotFound();

    if (!($product = Product::findOne(["id" => $id])))
      return $this->redirectNotFound();

    $product->delete();
    return $this->redirect("/mes-articles");
  }

  public function search(Request $req)
  {
    $keywords = $req->get("keywords");

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
