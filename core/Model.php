<?php

namespace app\core;

abstract class Model
{
  public const DB_TABLE = self::DB_TABLE;
  public const ALLOWED_FILE_TYPES = ["image/jpeg", "image/png", "image/jpeg", "image/gif"];
  protected ?array $files;
  protected array $errors;

  protected function addError(string $error): void
  {
    $this->errors[] = $error;
  }

  public function getErrors(): array
  {
    return $this->errors;
  }

  public function setFile(array $file): void
  {
    $this->file = $file;
  }

  protected function checkFile(): void
  {
    if (!$this->file) return;
    if ($this->file["size"] > 2e6)
      $this->addError("L'image ne doit pas dépasser 2 Mo.");
    if (!in_array($this->file["type"], self::ALLOWED_FILE_TYPES))
      $this->addError("L'image doit être au format jpeg, png ou gif.");
  }

  protected function saveImage(bool $isNew): void
  {
    if (!$this->file && !$isNew) return;
    if (!$this->file && $isNew) {
      $this->image = "_default.jpg";
      return;
    }
    $extension = pathinfo($this->file["name"], PATHINFO_EXTENSION);
    $image_folder = Application::$ROOT_DIR . "/public/build/img/" . self::DB_TABLE;
    $file_name = md5(time()) . ".$extension";
    $this->file["file_name"] = $file_name;
    $this->image = $file_name;
    move_uploaded_file($this->file["tmp_name"], "$image_folder/$file_name");
  }
}
