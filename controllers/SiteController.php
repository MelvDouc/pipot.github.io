<?php

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\models\Product;

class SiteController extends Controller
{
  public function home()
  {
    return $this->render("home");
  }

  public function categories()
  {
    $categories = Application::$instance->database->findAll("categories");

    return $this->render("categories/all", [
      "categories" => $categories
    ]);
  }

  public function category()
  {
    $id = $this->getParamId();

    if (!$id)
      return $this->redirectNotFound();

    $category = Application::$instance->database->findOne("categories", ["*"], ["id" => $id]);
    if (!$category)
      return $this->redirectNotFound();
   
    $products = Application::$instance->database->findAll(Product::DB_TABLE, ["*"], ["category_id" => $category["id"]]);
    
    return $this->render("categories/single", [
      "category" => $category,
      "products" => $products
    ]);
  }
}