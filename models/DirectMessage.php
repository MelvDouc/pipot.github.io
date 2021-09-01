<?php

namespace app\models;

use app\core\Application;
use app\core\Model;

class DirectMessage extends Model
{
  public const ERROR_RECIPIENT_NOT_EXISTS = "Aucun utilisateur ne correspond à ce destinataire.";
  public const ERROR_SELF_MESSAGE = "Vous ne pouvez pas vous envoyer un message à vous-même.";
  private int $sender_id;
  private ?string $recipient = null;
  private ?int $recipient_id = null;
  private ?string $subject = null;
  private ?string $content = null;

  public function __construct(int $sender_id, array $body)
  {
    $this->sender_id = $sender_id;

    foreach ($body as $key => $value)
      if (property_exists(self::class, $key))
        $this->{$key} = $value;
    $this->setRecipientId();
  }

  public function validate(): string | int
  {
    if (!$this->recipient || !$this->subject || !$this->content)
      return parent::ERROR_EMPTY_FIELDS;
    if (!$this->recipient_id)
      return self::ERROR_RECIPIENT_NOT_EXISTS;
    if ($this->recipient_id === $this->sender_id)
      return self::ERROR_SELF_MESSAGE;
    return 1;
  }

  public function send(): bool
  {
    return Application::$instance
      ->database
      ->addMessage($this);
  }

  public function getSenderId(): int
  {
    return $this->sender_id;
  }

  public function getRecipientId(): ?int
  {
    return $this->recipient_id;
  }

  private function setRecipientId(): void
  {
    if (!$this->recipient) return;

    $id_search = Application::$instance
      ->database
      ->findOne(User::DB_TABLE, ["id"], ["username" => $this->recipient]);
    $this->recipient_id = $id_search ? (int)$id_search["id"] : null;
  }

  public function getSubject(): ?string
  {
    return $this->subject;
  }

  public function getContent(): ?string
  {
    return $this->content;
  }
}
