<?php

namespace app\models;

use app\core\Application;
use app\core\Email;

class User
{
  public int $id;
  public string $username;
  public string $email;
  public string $password;
  private array $passwords = [];
  public string $role;
  public ?string $first_name;
  public ?string $last_name;
  public ?string $postal_address;
  public ?string $city;
  public ?string $zip_code;
  public ?string $phone_number;
  public ?string $verification_string;
  public int $is_account_active;
  public string $profile_pic;
  public string $added_at;
  private array $errors = [];

  public static function findOne(array $values, string $connector = "AND"): ?User
  {
    $dbUser = Application::$instance->database->findOne("users", ["*"], $values, $connector);
    if (!$dbUser)
      return null;
    $user = new User();
    $user->id = (int) $dbUser["id"];
    $user->username = $dbUser["username"];
    $user->email = $dbUser["email"];
    $user->password = $dbUser["password"];
    $user->role = $dbUser["role"];
    $user->first_name = $dbUser["first_name"];
    $user->last_name = $dbUser["last_name"];
    $user->postal_address = $dbUser["postal_address"];
    $user->city = $dbUser["city"];
    $user->zip_code = $dbUser["zip_code"];
    $user->phone_number = $dbUser["phone_number"];
    $user->verification_string = $dbUser["verification_string"];
    $user->is_account_active = $dbUser["is_account_active"];
    $user->profile_pic = $dbUser["profile-pic"];
    $user->added_at = $dbUser["added_at"];
    return $user;
  }

  public function __construct()
  {
    $this->profile_pic = "_default.jpg";
  }

  private function addError($error): void
  {
    $this->errors[] = $error;
  }

  public function getErrors(): array
  {
    return $this->errors;
  }

  private function checkUsername(): void
  {
    if (!$this->username) {
      $this->addError("Veuillez renseigner un nom d'utilisateur.");
      return;
    }
    $usernameExists = Application::$instance
      ->database
      ->valueExists("users", "username", $this->username);
    if ($usernameExists) {
      $this->addError("Nom d'utilisateur indisponible.");
      return;
    }
    if (!preg_match("/^[a-zA-Z0-9\_]{6,25}$/", $this->username))
      $this->addError(
        "Le nom d'utilisateur doit contenir entre 6 et 25 caractères, lesquels doivent êtres des lettres, des chiffres ou des tirets bas."
      );
  }

  private function checkEmail(): void
  {
    if (!$this->email) {
      $this->addError("Veuillez renseigner une adresse email.");
      return;
    }
    $emailExists = Application::$instance
      ->database
      ->valueExists("users", "email", $this->email);
    if ($emailExists) {
      $this->addError("Vous avez déjà un compte.");
      return;
    }
    if (!filter_var($this->email, FILTER_VALIDATE_EMAIL))
      $this->addError("Adresse email invalide.");
  }

  public function setPasswords(string $key, string $value): User
  {
    $this->passwords[$key] = $value;
    return $this;
  }

  public function hashPassword(): void
  {
    $this->password = password_hash($this->password, PASSWORD_DEFAULT);
  }

  public function checkPasswords(): void
  {
    $plain = $this->passwords["plain"];
    $confirm = $this->passwords["confirm"];
    if (!$plain)
      $this->addError("Veuillez renseigner un mot de passe.");
    if (!$confirm)
      $this->addError("Veuillez confirmer le mot de passe.");
    if ($plain && !preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,25}$/", $plain))
      $this->addError("Le mot de passe doit contenir entre 8 et 25 caractères dont au moins une minuscule, une majuscule et un chiffre.");
    if ($plain && $confirm && $plain !== $confirm)
      $this->addError("Les mots de passe ne se correspondent pas");
  }

  public function comparePassword(): bool
  {
    if (!isset($this->passwords["plain"]))
      return false;
    return password_verify($this->passwords["plain"], $this->password);
  }

  public function setVerificationString(): void
  {
    $alphabet = "abcdefghijklmnopqrstuvwxyz";
    $verifString = "";
    while (strlen($verifString) < 128) {
      $isDigit = random_int(0, 1);
      if ($isDigit) {
        $verifString .= random_int(0, 9);
        continue;
      }
      $randomLetter = $alphabet[random_int(0, 25)];
      $isUppercase = random_int(0, 1);
      $verifString .= $isUppercase ? strtoupper($randomLetter) : $randomLetter;
    }
    $this->verification_string = $verifString;
  }

  public function isValid(): bool
  {
    $this->checkUsername();
    $this->checkEmail();
    $this->checkPasswords();
    return count($this->errors) === 0;
  }

  public function save(): bool
  {
    $this->role = "USER";
    $this->hashPassword();
    $this->setVerificationString();
    return Application::$instance
      ->database
      ->add(
        "users",
        [
          "username" => $this->username,
          "email" => $this->email,
          "password" => $this->password,
          "role" => $this->role,
          "verification_string" => $this->verification_string
        ]
      );
  }

  public function sendConfirmation(): void
  {
    $email = new Email($this->email, $this->username, "Confirmation de création de compte");
    $email->set_confirmation_alt_body($this->verification_string);
    $email->set_confirmation_HTML_body($this->verification_string);
    $email->send();
  }
}
