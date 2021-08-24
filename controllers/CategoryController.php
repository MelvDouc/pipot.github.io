<?php

namespace app\controllers;

use app\core\Controller;
use app\core\Application;
use app\core\Request;

class CategoryController extends Controller
{
  public function categories()
  {
    $categories = Application::$instance
      ->database
      ->findAll("categories");

    return $this->render("categories/all", [
      "categories" => $categories
    ]);
  }

  public function category(Request $request)
  {
    $id = $request->getParamId();
    if (!$id)
      return $this->redirectNotFound();

    $category = Application::$instance
      ->database
      ->findOne("categories", ["*"], ["id" => $id]);
    if (!$category)
      return $this->redirectNotFound();

    $products = Application::$instance
      ->database
      ->findCategoryProducts((int)$category["id"]);

    return $this->render("categories/single", [
      "category" => $category,
      "products" => $products
    ]);
  }
}
