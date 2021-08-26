<?php

namespace app\models;

use app\core\Application;
use app\core\Model;

class Product extends Model
{
  public const DB_TABLE = "products";
  public const DB_COLUMNS = [
    "name" => [
      "updatable" => true,
      "type" => "string",
      "from_post" => true
    ],
    "description" => [
      "updatable" => true,
      "type" => "string",
      "from_post" => true
    ],
    "price" => [
      "updatable" => true,
      "type" => "integer",
      "from_post" => true
    ],
    "quantity" => [
      "updatable" => true,
      "type" => "integer",
      "from_post" => true
    ],
    "seller_id" => [
      "updatable" => false,
      "type" => "integer",
      "from_post" => false
    ],
    "category_id" => [
      "updatable" => true,
      "type" => "integer",
      "from_post" => true
    ],
    "image" => [
      "updatable" => true,
      "type" => "string",
      "from_post" => false
    ],
    "creation_date" => [
      "updatable" => false,
      "type" => "string",
      "from_post" => false
    ],
  ];
  public const NAME_MAX_LENGTH = 50;
  public const DEFAULT_IMAGE = "_default.jpg";
  public const VALID_IMAGE_TYPES = ["image/jpeg", "image/png", "image/jpeg", "image/gif"];
  public const MAX_IMAGE_SIZE = 2e6;
  public const ERROR_NAME_TOO_LONG = "Le nom du produit ne doit pas dépasser " . self::NAME_MAX_LENGTH . " caractères.";
  public const ERROR_PRICE_NOT_INTEGER = "Le prix du produit doit être un nombre entier.";
  public const ERROR_QUANTIY_NOT_INTEGER = "La quantité doit être un nombre entier.";
  public const ERROR_INVALID_FILE_TYPE = "L'image doit être au format jpeg, png ou gif.";
  public const ERROR_FILE_TOO_LARGE = "Le fichier image ne doit pas faire plus de 2 MO.";
  public const ERROR_NO_CATEGORY = "Catégorie non trouvée.";
  private string | null $name;
  private string | null $description;
  private int | null $price;
  private int | null $quantity = 1;
  private int $seller_id;
  private int | null $category_id;
  private string $image = "";
  private array | null $image_info;
  private bool $delete_image;

  public function __construct(array $post, array $files, int $seller_id)
  {
    foreach (self::DB_COLUMNS as $name => $value) {
      if (!$value["from_post"])
        continue;
      if (!array_key_exists($name, $post)) {
        $this->{$name} = null;
        continue;
      }
      $value = $post[$name];
      $this->{$name} = (is_numeric($value)) ? (int)$value : $value;
    }

    $this->seller_id = (int)$seller_id;
    $this->image_info = $files["image"] ?? null;
    $this->delete_image = $post["delete_image"] ?? false;
  }

  public function getName(): string | null
  {
    return $this->name;
  }

  public function getDescription(): string | null
  {
    return $this->description;
  }

  public function getPrice(): int | null
  {
    return $this->price;
  }

  public function getQuantity(): int | null
  {
    return $this->quantity;
  }

  public function getSellerId(): int | null
  {
    return $this->seller_id;
  }

  public function getCategoryId(): int | null
  {
    return $this->category_id;
  }

  public function getImage(): string
  {
    return $this->image;
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

  private function setDefaultImage(): void
  {
    $this->image = self::DEFAULT_IMAGE;
  }

  private function moveImageFile(): void
  {
    $extension = pathinfo($this->image_info["name"], PATHINFO_EXTENSION);
    $image_folder = Application::$ROOT_DIR . "/public/build/img/products";
    $file_name = md5(time()) . ".$extension";
    move_uploaded_file($this->image_info["tmp_name"], "$image_folder/$file_name");
    $this->image = $file_name;
  }

  private function saveImage(): void
  {
    if ($this->image_info["error"] === 4) {
      $this->setDefaultImage();
      return;
    }
    $this->moveImageFile();
  }

  public function save(): void
  {
    $this->saveImage();
    Application::$instance->database->addProduct($this);
  }

  private function updateImage(): void
  {
    if ($this->delete_image) {
      $this->setDefaultImage();
      return;
    }
    if (!$this->image_info["name"])
      return;
    $this->moveImageFile();
  }

  public function updateFrom(array $old_product): bool
  {
    $this->updateImage();
    $updated_columns = [];
    foreach (self::DB_COLUMNS as $name => $value) {
      if (
        !$value["updatable"]
        || $this->{$name} == $old_product[$name]
        || $name === "image" && !$this->image
      ) continue;
      $updated_columns[$name] = $this->{$name};
    }
    $id = (int)$old_product["id"];
    return Application::$instance
      ->database
      ->updateProduct($id, $updated_columns);
  }
}
