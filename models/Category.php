<?php

namespace app\models;

use app\core\Application;
use app\core\Model;

class Category extends Model
{
  public const DB_TABLE = "categories";
  public int $id;
  public string $name;
  public string $description;
  public string $image;
  public string $added_at;

  public static function findOne(array $values, string $connector = "AND"): ?Category
  {
    $dbCategory = Application::$instance
      ->database
      ->findOne(self::DB_TABLE, ["*"], $values, $connector);
    if (!$dbCategory) return null;
    $category = new Category();
    $category->id = $dbCategory["id"];
    $category->name = $dbCategory["name"];
    $category->description = $dbCategory["description"];
    $category->image = $dbCategory["image"];
    $category->added_at = $dbCategory["added_at"];
    return $category;
  }

  public static function findAll(): ?array
  {
    return Application::$instance
      ->database
      ->findAll(self::DB_TABLE);
  }
}
