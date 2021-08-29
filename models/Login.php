<?php

namespace app\models;

use app\core\Model;
use app\models\forms\Form;
use app\core\Application;

class Login extends Model
{
  public const ERROR_WRONG_CREDENTIALS = "Identifiants invalides.";
  public const ERROR_INACTIVE_ACCOUNT = "Vous n'avez pas encore activé votre compte.";
  public const ERROR_NOT_ADMIN = "Vous n'êtes pas administrateur.";
  private string $uuid;
  private string $password;
  private array | false $user;

  public function __construct(array $body)
  {
    $this->uuid = $body["uuid"] ?? null;
    $this->password = $body["password"] ?? null;
  }

  public static function getForm(string $action): Form
  {
    $form = new Form();
    $form->start($action, false, "login-form");
    $form->add_input("Nom d'utilisateur ou adresse email", "uuid", "text");
    $form->add_input("Mot de passe", "password");
    $form->add_submit("Se connecter");
    $form->end();
    return $form;
  }

  private function findUserInDatabase(): array | false
  {
    return Application::$instance->database->findOne(
      User::DB_TABLE,
      ["*"],
      [
        "username" => $this->uuid,
        "email" => $this->uuid,
      ],
      "OR"
    );
  }

  private function validateCredentials(): bool
  {
    return $this->user
      && password_verify($this->password, $this->user["password"]);
  }

  public function validate(): string | int
  {
    if (!$this->uuid || !$this->password)
      return parent::ERROR_EMPTY_FIELDS;

    $this->user = $this->findUserInDatabase();

    if (!$this->validateCredentials())
      return self::ERROR_WRONG_CREDENTIALS;

    if ((int)$this->user["is_account_active"] !== 1) {
      return self::ERROR_INACTIVE_ACCOUNT;
    }

    return 1;
  }

  public function validateAdmin(): string | int
  {
    if (!$this->uuid || !$this->password)
      return parent::ERROR_EMPTY_FIELDS;

    $this->user = $this->findUserInDatabase();

    if ($this->user && (int)$this->user["role"] !== User::ROLES["ADMIN"])
      return self::ERROR_NOT_ADMIN;

    if (!$this->validateCredentials())
      return self::ERROR_WRONG_CREDENTIALS;

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
