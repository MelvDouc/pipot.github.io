<?php

namespace app\core;

class Model
{
  public const ERROR_EMPTY_FIELDS = "Veuillez remplir tous les champs requis.";

  public static function DbColumn(bool $updatable, string $type, bool $from_post)
  {
    return [
      "updatable" => $updatable,
      "type" => $type,
      "from_post" => $from_post,
    ];
  }
}
