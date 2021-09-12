<?php

namespace app\models;

use app\core\Application;

class Product
{
  public int $id;
  public string $name;
  public string $description;
  public int $price;
  public int $quantity;
  public int $seller_id;
  public int $category_id;
  public string $image;
  public string $added_at;
  private array $errors = [];

  private static function findOne($values, $connector = "AND"): ?Product
  {
    $dbProduct = Application::$instance->database->findOne("products", ["*"], $values, $connector);
    if (!$dbProduct)
      return null;
    $product = new Product();
    $product->id = (int) $dbProduct["id"];
    $product->name = $dbProduct["name"];
    $product->description = $dbProduct["description"];
    $product->price = (int) $dbProduct["price"];
    $product->quantity = (int) $dbProduct["quantity "];
    $product->seller_id = (int) $dbProduct["seller_id"];
    $product->category_id = (int) $dbProduct["category_id"];
    $product->image = $dbProduct["image"];
    $product->added_at = $dbProduct["added_at"];
    return $product;
  }

  public function __construct()
  {
    $this->image = "_default.jpg";
  }

  private function addError($error): void
  {
    $this->errors[] = $error;
  }

  public function getErrors(): array
  {
    return $this->errors;
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

  private function isValid(): bool
  {
    $this->checkName();
    $this->checkDescription();
    $this->checkPrice();
    $this->checkQuantity();
    $this->checkCategory();
    return count($this->errors) === 0;
  }

  public function save(): bool
  {
    return Application::$instance
      ->database
      ->add(
        "products",
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
}
