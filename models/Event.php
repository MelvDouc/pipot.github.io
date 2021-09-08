<?php

namespace app\models;

use DateTime;
use app\core\Model;
use app\core\Application;

define("EVENT_DB_COLUMNS", [
  "name" => Model::DbColumn(true, "string", true),
  "description" => Model::DbColumn(true, "string", true),
  "author_id" => Model::DbColumn(false, "integer", false),
  "start_date" => Model::DbColumn(true, "string", true),
  "end_date" => Model::DbColumn(true, "string", true),
  "image" => Model::DbColumn(true, "string", true)
]);

class Event extends Model
{
  public const DB_TABLE = "events";
  public const DB_COLUMNS = EVENT_DB_COLUMNS;
  public const NAME_MAX_LENGTH = 50;
  public const DATE_FORMAT = "Y-m-d\TH:i";
  public const ERROR_NAME_TOO_LONG = "Le nom du produit ne doit pas dépasser " . self::NAME_MAX_LENGTH . " caractères.";
  public const ERROR_INVALID_DATE_FORMAT = "Format de date invalide.";
  public const ERROR_INVALID_START_OR_END = "L'événement ne peut pas commencer ni finir avant aujourd'hui.";
  public const ERROR_INVALID_END = "L'événement ne peut pas finir avant sa date de début.";
  private ?string $name;
  private ?string $description;
  private int $author_id;
  private ?string $start_date;
  private ?string $end_date;
  private ?array $image = null;

  public function __construct(array $body, int $author_id)
  {
    $this->name = $body["name"] ?? null;
    $this->description = $body["description"] ?? null;
    $this->start_date = $body["start_date"] ?? null;
    $this->end_date = $body["end_date"] ?? null;
    if (array_key_exists("image", $body["files"]) && $body["files"]["image"]["error"] !== 4)
      $this->image = $body["files"]["image"];
    $this->author_id = $author_id;
  }

  private function validate_post_data(): int | string
  {
    if (!$this->name || !$this->description || !$this->start_date || !$this->end_date)
      return parent::ERROR_EMPTY_FIELDS;
    if (strlen($this->name) > self::NAME_MAX_LENGTH)
      return self::ERROR_NAME_TOO_LONG;
    return 1;
  }

  private function validateDates(): int | string
  {
    if (!$this->isDateValid($this->start_date) || !$this->isDateValid($this->start_date))
      return self::ERROR_INVALID_DATE_FORMAT;
    $startTimestamp = strtotime($this->start_date);
    $endTimeStamp = strtotime($this->end_date);
    $now = time();
    if ($startTimestamp < $now || $endTimeStamp < $now)
      return self::ERROR_INVALID_START_OR_END;
    if ($startTimestamp > $endTimeStamp)
      return self::ERROR_INVALID_END;
    return 1;
  }

  private function validateImage(): string | int
  {
    if (!$this->image)
      return 1;
    if ($this->image["size"] > parent::MAX_IMAGE_SIZE)
      return parent::ERROR_FILE_TOO_LARGE;
    if (!in_array($this->image["type"], parent::VALID_IMAGE_TYPES))
      return parent::ERROR_INVALID_FILE_TYPE;
    return 1;
  }

  private function saveImage(): void
  {
    $extension = pathinfo($this->image["name"], PATHINFO_EXTENSION);
    $image_folder = Application::$ROOT_DIR . "/public/build/img/events";
    $file_name = md5(time()) . ".$extension";
    $this->image["file_name"] = $file_name;
    move_uploaded_file($this->image["tmp_name"], "$image_folder/$file_name");
  }

  private function updateImage(): void
  {
    if (!$this->image) return;
    $this->saveImage();
  }

  private function isDateValid(string $dateString): bool
  {
    $formatted = DateTime::createFromFormat(self::DATE_FORMAT, $dateString);
    return $formatted && $formatted->format(self::DATE_FORMAT) === $dateString;
  }

  public function validate(): string | int
  {
    if (($postValidation = $this->validate_post_data()) !== 1)
      return $postValidation;

    if (($dateValidation = $this->validateDates()) !== 1)
      return $dateValidation;

    if (($imageValidation = $this->validateImage()) !== 1)
      return $imageValidation;

    return 1;
  }

  public function save(): bool
  {
    if ($this->image)
      $this->saveImage();

    return Application::$instance
      ->database
      ->addEvent($this);
  }

  public function updateFrom(array $oldEvent): bool
  {
    $this->updateImage();
    $updatedColumns = [];
    foreach (self::DB_COLUMNS as $name => $value) {
      $isUpdatable = $value["updatable"];
      $isValueUnchanged = $this->{$name} == $oldEvent[$name];
      if (!$isUpdatable || $isValueUnchanged)
        continue;
      if ($name !== "image")
        $updatedColumns[$name] = $this->{$name};
      else {
        if (!$this->image) continue;
        $updatedColumns["image"] = $this->image["file_name"];
      }
    }
    $id = (int) $oldEvent["id"];
    return Application::$instance
      ->database
      ->updateEvent($id, $updatedColumns);
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function getDescription(): ?string
  {
    return $this->description;
  }

  public function getAuthorId(): int
  {
    return $this->author_id;
  }

  public function getStartDate(): ?string
  {
    return $this->start_date;
  }

  public function getEndDate(): ?string
  {
    return $this->end_date;
  }

  public function getImage(): ?string
  {
    return $this->image["file_name"] ?? null;
  }
}
