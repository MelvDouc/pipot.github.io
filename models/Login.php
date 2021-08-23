<?php

namespace app\models;

use app\core\Model;
use app\core\Application;

class Login extends Model
{
  public const ERROR_WRONG_CREDENTIALS = "Identifiants invalides.";
  public const ERROR_INACTIVE_ACCOUNT = "Vous n'avez pas encore activé votre compte.";
  private string $uuid;
  private string $password;
  private array | false $user;

  public function __construct($post)
  {
    $this->uuid = $post["uuid"] ?? null;
    $this->password = $post["password"] ?? null;
  }

  public function validate(): string | int
  {
    if (!$this->uuid || !$this->password)
      return parent::ERROR_EMPTY_FIELDS;

    $this->user = Application::$instance->database->findOne(
      User::DB_TABLE,
      ["*"],
      [
        "username" => $this->uuid,
        "email" => $this->uuid,
      ],
      "OR"
    );

    if (!$this->user || !password_verify($this->password, $this->user["password"]))
      return self::ERROR_WRONG_CREDENTIALS;
    
    if ((int)$this->user["is_account_active"] !== 1) {
      return self::ERROR_INACTIVE_ACCOUNT;
    }

    return 1;
  }

  public function getUser(): array | false
  {
    return $this->user;
  }

  public function setLoggedUser(): void
  {
    Application::$instance->session->setUser($this->user);
  }
}