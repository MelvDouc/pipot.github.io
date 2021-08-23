<?php

namespace app\models;

use app\core\Application;
use app\core\Email;
use app\core\Model;

class User extends Model
{
  public const DB_TABLE = "users";
  private const USER_ROLES = [
    "ADMIN" => 0,
    "USER" => 1
  ];
  private const USERNAME_REGEX = "/^[a-zA-Z0-9\_]{6,25}$/";
  private const PASSWORD_REGEX = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,25}$/";
  private const ERROR_USERNAME_FORMAT = "Le nom d'utilisateur doit contenir entre 6 et 25 caractères, lesquels doivent êtres des lettres, des chiffres ou des tirets bas.";
  private const ERROR_USERNAME_EXISTS = "Nom d'utilisateur indisponible";
  private const ERROR_EMAIL_FORMAT = "Adresse email non valide.";
  private const ERROR_EMAIL_EXISTS = "Adresse email déjà utilisée.";
  private const ERROR_PASSWORD_FORMAT = "Le mot de passe doit contenir entre 8 et 25 caractères dont au moins une minuscule, une majuscule et un chiffre.";
  private const ERROR_PASSWORDS_NO_MATCH = "Les mots de passe ne sont pas identiques.";
  private const ERROR_TERMS_NOT_AGREED = "Veuillez accepter les conditions d'utilisation.";
  public string $username;
  public string $email;
  public string $plain_password;
  public string $confirm_password;
  public bool $agrees_terms;
  public int $role = self::USER_ROLES["USER"];
  public string $verification_string;

  public function __construct(array $post)
  {
    $this->username = $post["username"] ?? "";
    $this->email = $post["email"] ?? "";
    $this->plain_password = $post["password"] ?? "";
    $this->confirm_password = $post["confirm_password"] ?? "";
    $this->agrees_terms = array_key_exists("agree_terms", $post);
    $this->verification_string = md5(time());
  }

  private function validate_username(): string | int
  {
    if (!preg_match(self::USERNAME_REGEX, $this->username))
      return self::ERROR_USERNAME_FORMAT;
    if (Application::$instance->database->valueExists(self::DB_TABLE, "username", $this->username))
      return self::ERROR_USERNAME_EXISTS;
    return 1;
  }

  private function validate_email(): string | int
  {
    if (!filter_var($this->email, FILTER_VALIDATE_EMAIL))
      return self::ERROR_EMAIL_FORMAT;
    if (Application::$instance->database->valueExists(self::DB_TABLE, "email", $this->email))
      return self::ERROR_EMAIL_EXISTS;
    return 1;
  }

  private function validate_password(): string | int
  {
    if (!preg_match(self::PASSWORD_REGEX, $this->plain_password))
      return self::ERROR_PASSWORD_FORMAT;
    if ($this->plain_password !== $this->confirm_password)
      return self::ERROR_PASSWORDS_NO_MATCH;
    return 1;
  }

  public function validate(): string | int
  {
    if (!$this->username || !$this->email || !$this->plain_password || !$this->confirm_password)
      return parent::ERROR_EMPTY_FIELDS;

    if (!$this->agrees_terms)
      return self::ERROR_TERMS_NOT_AGREED;

    $username_validation = $this->validate_username();
    if ($username_validation !== 1)
      return $username_validation;

    $email_validation = $this->validate_email();
    if ($email_validation !== 1)
      return $email_validation;

    $password_validation = $this->validate_password();
    if ($password_validation !== 1)
      return $password_validation;

    return 1;
  }

  public function save(): void
  {
    Application::$instance
      ->database
      ->addUser($this);
    unset($this->plain_password);
    unset($this->confirm_password);
  }

  public function send_verification(): void
  {
    $email = new Email($this->email, $this->username, "Confirmation d'inscription");
    $email->set_confirmation_HTML_body($this->verification_string);
    $email->set_confirmation_alt_body($this->verification_string);
    $email->send();
  }
}