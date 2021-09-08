<?php

namespace app\core;

class Model
{
  public const ERROR_EMPTY_FIELDS = "Veuillez remplir tous les champs requis.";
  public const VALID_IMAGE_TYPES = ["image/jpeg", "image/png", "image/jpeg", "image/gif"];
  public const MAX_IMAGE_SIZE = 2e6;
  public const ERROR_INVALID_FILE_TYPE = "L'image doit être au format jpeg, png ou gif.";
  public const ERROR_FILE_TOO_LARGE = "Le fichier image ne doit pas faire plus de 2 MO.";

  public static function DbColumn(bool $updatable, string $type, bool $from_post)
  {
    return [
      "updatable" => $updatable,
      "type" => $type,
      "from_post" => $from_post,
    ];
  }

  protected function validate_file_data(): string | int
  {
    if (!$this->files) return 1;

    extract($this->files["image"]);

    if ($error === 4)
      return 1;
    if (!in_array($type, self::VALID_IMAGE_TYPES))
      return self::ERROR_INVALID_FILE_TYPE;
    if ($size > self::MAX_IMAGE_SIZE)
      return self::ERROR_FILE_TOO_LARGE;
    return 1;
  }
}
