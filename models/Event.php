<?php

namespace app\models;

use app\core\Application;
use app\core\Model;

class Event extends Model
{
  public const DB_TABLE = "events";
  public int $id;
  public string $name;
  public string $description;
  public int $author_id;
  public string $start_date;
  public string $end_date;
  public string $image;
  public string $added_at;

  public static function findOne($value, $connector = "AND"): ?Event
  {
    $dbEvent = Application::$instance
      ->database
      ->findOne(self::DB_TABLE, ["*"], $value, $connector);
    if (!$dbEvent)
      return null;
    $event = new Event();
    $event->id = (int) $dbEvent["id"];
    $event->name = $dbEvent["name"];
    $event->description = $dbEvent["description"];
    $event->author_id = (int) $dbEvent["author_id"];
    $event->start_date = $dbEvent["start_date"];
    $event->end_date = $dbEvent["end_date"];
    $event->image = $dbEvent["image"];
    $event->added_at = $dbEvent["added_at"];
    return $event;
  }

  public static function findAll(): ?array
  {
    return Application::$instance->database->findAll(self::DB_TABLE);
  }

  private function checkName(): void
  {
    if (!$this->name || strlen($this->name) > 50)
      $this->addError("Veuillez renseigner un nom de 50 caractères ou moins.");
  }

  private function checkDescription(): void
  {
    if (!$this->description)
      $this->addError("Veuillez décrire l'événement.");
  }

  private function checkDates(): void
  {
    $startTimestamp = strtotime($this->start_date);
    $endTimestamp = strtotime($this->end_date);
    if (!$startTimestamp)
      $this->addError("Veuillez renseigner une date de début valide.");
    if (!$endTimestamp)
      $this->addError("Veuillez renseigner une date de fin valide.");
    if (!$startTimestamp || !$endTimestamp) return;
    $now = time();
    if ($startTimestamp < $now)
      $this->addError("La date de début ne pas être antérieure à aujourd'hui.");
    if ($endTimestamp < $now)
      $this->addError("La date de fin ne pas être antérieure à aujourd'hui.");
    if ($startTimestamp > $endTimestamp)
      $this->addError("La date de fin ne peut pas être antérieure à la date de début.");
  }

  public function isValid(): bool
  {
    $this->checkName();
    $this->checkDescription();
    $this->checkDates();
    $this->checkFile();
    return count($this->errors) === 0;
  }

  public function save(): bool
  {
    $this->saveImage(true);
    $values = [
      "name" => $this->name,
      "description" => $this->description,
      "author_id" => $this->author_id,
      "start_date" => $this->start_date,
      "end_date" => $this->end_date,
      "image" => $this->image
    ];
    return Application::$instance
      ->database
      ->add(self::DB_TABLE, $values);
  }

  public function update(): bool
  {
    $this->saveImage(false);
    $values = [
      "name" => $this->name,
      "description" => $this->description,
      "author_id" => $this->author_id,
      "start_date" => $this->start_date,
      "end_date" => $this->end_date,
      "image" => $this->image
    ];
    return Application::$instance
      ->database
      ->update(self::DB_TABLE, $values, $this->id);
  }

  public function delete(): bool
  {
    return Application::$instance
      ->database
      ->delete(self::DB_TABLE, $this->id);
  }
}
