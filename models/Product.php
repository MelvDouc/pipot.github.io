<?php

namespace app\models;

use app\core\Model;
use app\core\Application;

class Product extends Model
{
  public const DB_TABLE = "products";
  public int $id;
  public string $name;
  public string $description;
  public int $price;
  public int $quantity;
  public int $seller_id;
  public int $category_id;
  public string $image;
  public string $added_at;

  private static function instantiate(array $dbProduct): Product
  {
    $product = new Product();
    $product->id = (int) $dbProduct["id"];
    $product->name = $dbProduct["name"];
    $product->description = $dbProduct["description"];
    $product->price = (int) $dbProduct["price"];
    $product->quantity = (int) $dbProduct["quantity"];
    $product->seller_id = (int) $dbProduct["seller_id"];
    $product->category_id = (int) $dbProduct["category_id"];
    $product->image = $dbProduct["image"];
    $product->added_at = $dbProduct["added_at"];
    return $product;
  }

  public static function findOne($values, $connector = "AND"): ?Product
  {
    $dbProduct = Application::$instance
      ->database
      ->findOne(self::DB_TABLE, ["*"], $values, $connector);
    if (!$dbProduct)
      return null;
    return self::instantiate($dbProduct);
  }

  public static function findAll(array $where = [], $connector = "AND")
  {
    $dbProducts = Application::$instance
      ->database
      ->findAll(self::DB_TABLE, ["*"], $where, $connector);
    return array_map(fn ($dbProduct) => self::instantiate($dbProduct), $dbProducts);
  }

  public function getSellerUsername(): string
  {
    $seller = User::findOne(["id" => $this->seller_id]);
    return $seller->username;
  }

  public function getCategoryName(): string
  {
    $category = Category::findOne(["id" => $this->category_id]);
    return $category->name;
  }

  private function checkName(): void
  {
    if (!$this->name) {
      $this->addError("Veuillez donner un nom à l'article.");
      return;
    }
    if (strlen($this->name) > 50)
      $this->addError("Le nom de l'article ne doit pas dépasser 50 caractères.");
  }

  private function checkDescription(): void
  {
    if (!$this->description)
      $this->addError("Veuillez décrire l'article.");
  }

  private function checkPrice(): void
  {
    if (!$this->price || gettype($this->price) !== "integer")
      $this->addError("Le prix doit être un nombre entier supérieur ou égal à 1.");
  }

  private function checkQuantity(): void
  {
    if (!$this->quantity || gettype($this->quantity) !== "integer")
      $this->addError("La quantité doit être un nombre entier supérieur ou égal à 1.");
  }

  public function getCategory(): ?Category
  {
    if (!$this->category_id) return null;
    return Category::findOne(["id" => $this->category_id]);
  }

  private function checkCategory(): void
  {
    if (!$this->getCategory())
      $this->addError("Catégorie non trouvée.");
  }

  public function isValid(): bool
  {
    $this->checkName();
    $this->checkDescription();
    $this->checkPrice();
    $this->checkQuantity();
    $this->checkCategory();
    $this->checkFile();
    return count($this->errors) === 0;
  }

  public function save(): bool
  {
    $this->saveImage(true);
    return Application::$instance
      ->database
      ->add(
        self::DB_TABLE,
        [
          "name" => $this->name,
          "description" => $this->description,
          "price" => $this->price,
          "quantity" => $this->quantity,
          "seller_id" => $this->seller_id,
          "category_id" => $this->category_id,
          "image" => $this->image
        ]
      );
  }

  public function update(): bool
  {
    $this->saveImage(false);
    return Application::$instance
      ->database
      ->update(
        self::DB_TABLE,
        [
          "name" => $this->name,
          "description" => $this->description,
          "price" => $this->price,
          "quantity" => $this->quantity,
          "seller_id" => $this->seller_id,
          "category_id" => $this->category_id,
          "image" => $this->image
        ],
        $this->id
      );
  }

  public function delete(): bool
  {
    return Application::$instance
      ->database
      ->delete(self::DB_TABLE, $this->id);
  }
}
