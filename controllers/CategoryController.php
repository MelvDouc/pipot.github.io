<?php

namespace app\controllers;

use app\core\Controller;
use app\core\Request;
use app\models\Category;
use app\models\Product;

class CategoryController extends Controller
{
  public function index(Request $request)
  {
    if (!($id = $request->getParamId())) {
      $categories = Category::findAll();
      return $this->render("categories/all", [
        "title" => "Catégories",
        "categories" => $categories,
      ]);
    }

    if (!($category = Category::findOne(["id" => $id])))
      return $this->redirectNotFound();

    return $this->render("categories/single", [
      "title" => "Catégorie $category->name",
      "category" => $category,
      "products" => Product::findAll(["category_id" => $id])
    ]);
  }
}
