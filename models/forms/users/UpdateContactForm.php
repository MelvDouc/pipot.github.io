<?php

namespace app\models\forms\users;

use app\models\forms\Form;

class updateContactForm extends Form
{
  private const DB_COLUMNS = ["postal_address", "city", "zip_code", "phone_number"];
  private array $user;
  private array $body;

  public function __construct(array $user)
  {
    $this->user = $user;
  }

  public function createView(): string
  {
    extract($this->user);

    $form = new Form();
    $form->start("/modifier-mes-coordonnees", true);
    $form->add_input("Adresse postale", "postal_address", "text", false, [
      "maxlength" => 255,
      "value" => $postal_address
    ]);
    $form->add_input("Ville", "city", "text", false, [
      "maxlength" => 50,
      "value" => $city
    ]);
    $form->add_input("Code postal", "zip_code", "text", false, [
      "maxlength" => 10,
      "value" => $zip_code
    ]);
    $form->add_input("Numéro de téléphone", "phone_number", "text", false, [
      "maxlength" => 20,
      "value" => $phone_number
    ]);
    $form->add_submit();
    $form->end();
    return $form->createView();
  }

  public function getBody(): array
  {
    foreach ($this->body as $key => $value)
      if (!in_array($key, self::DB_COLUMNS))
        unset($this->body[$key]);
    return $this->body;
  }

  public function setBody(array $value): void
  {
    $this->body = $value;
  }

  private function isLengthValid(string $key, int $maxLength): bool
  {
    if (!array_key_exists($key, $this->body))
      return true;
    return strlen($this->body[$key]) <= $maxLength;
  }

  public function validate(): string | int
  {
    if (!$this->isLengthValid("postal_address", 255))
      return "L'adresse postale ne doit pas dépasser 255 caractères.";
    if (!$this->isLengthValid("city", 50))
      return "La ville ne doit pas dépasser 50 caractères.";
    if (!$this->isLengthValid("zip_code", 10))
      return "Le code postal ne doit pas dépasser 50 caractères.";
    if (!$this->isLengthValid("phone_number", 20))
      return "Le numéro de téléphone ne doit pas dépasser 20 caractères.";
    return 1;
  }
}
