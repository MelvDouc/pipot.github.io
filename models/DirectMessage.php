<?php

namespace app\models;

use app\core\Application;
use app\core\Model;

class DirectMessage extends Model
{
  public const DB_TABLE = "direct_messages";
  public const ERROR_RECIPIENT_NOT_EXISTS = "Aucun utilisateur ne correspond à ce destinataire.";
  public const ERROR_SELF_MESSAGE = "Vous ne pouvez pas vous envoyer un message à vous-même.";
  public int $id;
  public int $sender_id;
  public int $recipient_id;
  public string $subject;
  public string $content;
  public string $added_at;

  public static function findAll(array $where): ?array
  {
    $dbMessages = Application::$instance
      ->database
      ->findAll(self::DB_TABLE, ["*"], $where);
    if (!$dbMessages) return null;
    return array_map(function ($dbMessage) {
      $instance = new DirectMessage();
      $instance->id = (int) $dbMessage["id"];
      $instance->sender_id = (int) $dbMessage["sender_id"];
      $instance->recipient_id = (int) $dbMessage["recipient_id"];
      $instance->subject = $dbMessage["subject"];
      $instance->content = $dbMessage["content"];
      $instance->added_at = $dbMessage["added_at"];
      return $instance;
    }, $dbMessages);
  }

  private function checkSubject(): void
  {
    if (!$this->subject || strlen($this->subject) > 50)
      $this->addError("Le sujet ne doit dépasser 50 caractères.");
  }

  private function checkContent(): void
  {
    if (!$this->content)
      $this->addError("Veuillez ajouter un message.");
  }

  private function checkRecipientId(): void
  {
    $recipient = User::findOne(["id" => $this->recipient_id]);
    if (!$recipient)
      $this->addError("Destinataire non trouvé.");
  }

  public function isValid(): bool
  {
    $this->checkSubject();
    $this->checkContent();
    $this->checkRecipientId();
    return count($this->errors) === 0;
  }

  public function save(): bool
  {
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
}
