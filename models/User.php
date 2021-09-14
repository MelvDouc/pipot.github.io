<?php

namespace app\models;

use app\core\Application;
use app\core\Email;
use app\core\Model;

class User extends Model
{
  public const DB_TABLE = "users";
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
  public string $image;
  public string $added_at;

  private static function instantiate(array $dbUser): User
  {
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
    $user->image = $dbUser["image"];
    $user->added_at = $dbUser["added_at"];
    return $user;
  }

  public static function findOne(array $values, string $connector = "AND"): ?User
  {
    $dbUser = Application::$instance->database->findOne(self::DB_TABLE, ["*"], $values, $connector);
    if (!$dbUser)
      return null;
    return self::instantiate($dbUser);
  }

  public static function findAll()
  {
    $dbUsers = Application::$instance->database->findAll(self::DB_TABLE);
    return array_map(fn ($dbUser) => self::instantiate($dbUser), $dbUsers);
  }

  public function getBasket(): ?array
  {
    return Application::$instance
      ->database
      ->findBasket($this->id);
  }

  public function hasInBasket(Product $product): bool
  {
    $basket = $this->getBasket();
    if (!$basket) return false;
    foreach ($basket as $basketItem)
      if ($basketItem->product_id === $product->id)
        return true;
    return false;
  }

  public function addToBasket(Product $product): bool
  {
    return Application::$instance
      ->database
      ->add(
        "basket",
        [
          "product_id" => $product->id,
          "buyer_id" => $this->id
        ]
      );
  }

  public function removeFromBasket(Product $product): bool
  {
    return Application::$instance
      ->database
      ->delete(
        "basket",
        [
          "product_id" => $product->id,
          "buyer_id" => $this->id
        ]
      );
  }

  public function getProducts(): ?array
  {
    return Product::findAll(["seller_id" => $this->id]);
  }

  public function getAverageScore(): ?float
  {
    return Application::$instance
      ->database
      ->findAverageScore($this->id);
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

  private function checkContact(): void
  {
    $contactProperties = [
      ["first_name", 50, "Le prénom"],
      ["last_name", 50, "Le nom de famille"],
      ["postal_address", 25, "L'adresse postale"],
      ["city", 50, "La ville"],
      ["zip_code", 10, "Le code postal"],
      ["phone_number", 20, "Le numéro de téléphone"]
    ];
    foreach ($contactProperties as $property) {
      $name = $property[0];
      $maxlength = $property[1];
      $translation = $property[2];
      if (!$this->{$name}) continue;
      if (strlen($this->{$name}) > $maxlength)
        $this->addError("$translation ne doit pas dépasser $maxlength caractères.");
    }
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
    $this->checkFile();
    return count($this->errors) === 0;
  }

  public function isPasswordUpdateValid(): bool
  {
    $this->errors = [];
    $old = $this->passwords["old"] ?? null;
    $new = $this->passwords["new"] ?? null;
    $confirm = $this->passwords["confirm"] ?? null;
    if (!$old)
      $this->addError("Veuillez renseigner votre ancien mot de passe.");
    if (!$new)
      $this->addError("Veuillez renseigner un nouveau mot de passe.");
    if (!$confirm)
      $this->addError("Veuillez confirmer le nouveau mot de passe.");
    if ($old && !password_verify($old, $this->password))
      $this->addError("Ancien mot de passe incorrect.");
    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,25}$/", $new))
      $this->addError("Le nouveau mot de passe doit contenir entre 8 et 25 caractères dont au moins une minuscule, une majuscule et un chiffre.");
    if ($new && $confirm && $new !== $confirm)
      $this->addError("Le mot de passe de confirmation ne correspond pas au nouveau mot de passe.");
    return count($this->errors) === 0;
  }

  public function isContactUpdateValid(): bool
  {
    $this->errors = [];
    $this->checkContact();
    return count($this->errors) === 0;
  }

  public function save(): bool
  {
    $this->role = "USER";
    $this->hashPassword();
    $this->saveImage(true);
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
          "verification_string" => $this->verification_string,
          "image" => $this->image
        ]
      );
  }

  public function updatePassword(): bool
  {
    $this->password = $this->passwords["new"];
    $this->hashPassword();
    return Application::$instance
      ->database
      ->update(
        self::DB_TABLE,
        ["password" => $this->password],
        $this->id
      );
  }

  public function updateContact(): bool
  {
    return Application::$instance
      ->database
      ->update(
        self::DB_TABLE,
        [
          "first_name" => $this->first_name,
          "last_name" => $this->last_name,
          "postal_address" => $this->postal_address,
          "city" => $this->city,
          "zip_code" => $this->zip_code,
          "phone_number" => $this->phone_number,
        ],
        $this->id
      );
  }

  public function activateAcouunt(): bool
  {
    return Application::$instance
      ->database
      ->update(
        self::DB_TABLE,
        [
          "is_account_active" => 1,
          "verification_string" => null
        ],
        $this->id
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
