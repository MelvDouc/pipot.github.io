<?php

namespace app\models;

use app\core\Application;
use app\core\Model;

class Product extends Model
{
  public const DB_TABLE = "products";
  public const NAME_MAX_LENGTH = 50;
  public const VALID_IMAGE_TYPES = ["image/jpeg", "image/png", "image/jpeg", "image/gif"];
  public const MAX_IMAGE_SIZE = 2e6;
  public const ERROR_NAME_TOO_LONG = "Le nom du produit ne doit pas dépasser " . self::NAME_MAX_LENGTH . " caractères.";
  public const ERROR_PRICE_NOT_INTEGER = "Le prix du produit doit être un nombre entier.";
  public const ERROR_QUANTIY_NOT_INTEGER = "La quantité doit être un nombre entier.";
  public const ERROR_INVALID_FILE_TYPE = "L'image doit être au format jpeg, png ou gif.";
  public const ERROR_FILE_TOO_LARGE = "Le fichier image ne doit pas faire plus de 2 MO.";
  public const ERROR_NO_CATEGORY = "Catégorie non trouvée.";
  public string $name;
  public string $description;
  public int | null $price;
  public int | null $quantity = 1;
  public int $seller_id;
  public int $category_id;
  public string $image = "";
  private array $image_info;

  public function __construct(array $post, array $files, int $seller_id)
  {
    $is_price_valid = isset($post["price"]) && is_numeric($post["price"]);
    $is_quantity_valid = isset($post["quantity"]) && is_numeric($post["quantity"]);

    $this->name = $post["name"] ?? null;
    $this->description = $post["description"] ?? null;
    $this->price = ($is_price_valid) ? (int)$post["price"] : null;
    $this->quantity = ($is_quantity_valid) ? (int)$post["quantity"] : null;
    $this->category_id = (int)$post["category"];
    $this->image_info = $files["image"];
    $this->seller_id = $seller_id;
  }

  private function validate_post_data(): string | int
  {
    if (!$this->name || !$this->description)
      return parent::ERROR_EMPTY_FIELDS;
    if (strlen($this->name) > self::NAME_MAX_LENGTH)
      return self::ERROR_NAME_TOO_LONG;
    if (!is_int($this->price))
      return self::ERROR_PRICE_NOT_INTEGER;
    if (!is_int($this->quantity))
      return self::ERROR_QUANTIY_NOT_INTEGER;
    return 1;
  }

  private function validate_file_data(): string | int
  {
    extract($this->image_info);
    if ($error === 4)
      return 1;
    if (!in_array($type, self::VALID_IMAGE_TYPES))
      return self::ERROR_INVALID_FILE_TYPE;
    if ($size > self::MAX_IMAGE_SIZE)
      return self::ERROR_FILE_TOO_LARGE;
    return 1;
  }

  private function validate_category(): string | int
  {
    if (!Application::$instance
      ->database
      ->valueExists("categories", "id", $this->category_id))
      return self::ERROR_NO_CATEGORY;
    return 1;
  }

  public function validate(): string | int
  {
    $post_validation = $this->validate_post_data();
    if ($post_validation !== 1)
      return $post_validation;

    $file_validation = $this->validate_file_data();
    if ($file_validation !== 1)
      return $file_validation;

    $category_validation = $this->validate_category();
    if ($category_validation !== 1)
      return $category_validation;

    return 1;
  }

  private function saveImage(): void
  {
    if ($this->image_info["error"] === 4) {
      $this->image = "_default.jpg";
      return;
    }
    $extension = pathinfo($this->image_info["name"], PATHINFO_EXTENSION);
    $image_folder = Application::$ROOT_DIR . "/public/build/img/products";
    $file_name = md5(time()) . ".$extension";
    move_uploaded_file($this->image_info["tmp_name"], "$image_folder/$file_name");
    $this->image = $file_name;
  }

  public function save(): void
  {
    $this->saveImage();
    Application::$instance->database->addProduct($this);
  }
}
