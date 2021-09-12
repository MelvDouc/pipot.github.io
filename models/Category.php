<?php

namespace app\models;

use app\core\Application;

class Category
{
  public int $id;
  public string $name;
  public string $description;
  public string $image;
  public string $added_at;

  public static function findOne(array $values, string $connector = "AND"): ?Category
  {
    $dbCategory = Application::$instance
      ->database
      ->findOne("categories", ["*"], $values, $connector);
    if (!$dbCategory) return null;
    $category = new Category();
    $category->id = $dbCategory["id"];
    $category->name = $dbCategory["name"];
    $category->description = $dbCategory["description"];
    $category->image = $dbCategory["image"];
    $category->added_at = $dbCategory["added_at"];
    return $category;
  }
}
