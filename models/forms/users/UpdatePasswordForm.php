<?php

namespace app\models\forms;

use app\core\Model;
use app\models\User;

class UpdatePasswordForm extends Form
{
  private const FIELDS = ["old_password", "new_password", "confirm_new_password"];
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
    $form->start("/modifier-mon-mot-de-passe", true);
    $form->add_input("Ancien mot de passe", self::FIELDS[0], "password", true, [
      "maxlength" => 25,
    ]);
    $form->add_input("Nouveau mot de passe", self::FIELDS[1], "password", true, [
      "maxlength" => 25,
    ]);
    $form->add_input("Confirmer le nouveau mot de passe", self::FIELDS[2], "password", true, [
      "maxlength" => 25,
    ]);
    $form->add_submit();
    $form->end();
    return $form->createView();
  }

  public function getHashedPassword(): string
  {
    return password_hash($this->body["new_password"], PASSWORD_DEFAULT);
  }

  public function setBody(array $value): void
  {
    $this->body = $value;
  }

  public function validate(): string | int
  {
    foreach (self::FIELDS as $value)
      if (!array_key_exists($value, $this->body) || !$this->body[$value])
        return Model::ERROR_EMPTY_FIELDS;
    extract($this->body);
    if ($new_password === $old_password)
      return "Veuillez saisir un nouveau mot de passe.";
    if (!password_verify($old_password, $this->user["password"]))
      return "Ancien mot de passe incorrect.";
    if (!preg_match(User::PASSWORD_REGEX, $new_password))
      return User::ERROR_PASSWORD_FORMAT;
    if ($new_password !== $confirm_new_password)
      return User::ERROR_PASSWORDS_NO_MATCH;
    return 1;
  }
}
