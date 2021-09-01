<?php

namespace app\models\forms\users;

use app\models\forms\Form;
use app\models\User;

class UpdateUserForm
{
  private string $action;
  private array $user;

  public function __construct(string $action, array $user)
  {
    $this->action = $action;
    $this->user = $user;
  }

  public function createView(): string
  {
    $form = new Form();
    $form->start($this->action, true);
    $form->add_input("Nom d'utilisateur", "username", "text", true, [
      "maxlength" => 25,
      "value" => $this->user["username"]
    ]);
    $form->add_input("Adresse email", "email", "email");
    $form->add_select("Rôle", "role", User::ROLES, $this->user["role"]);
    $form->add_textarea("Adresse postale", "postal_address", false, $this->user["postal_address"]);
    $form->add_input("Ville", "city", "text", false);
    $form->add_input("Code postal", "postal_code", "text", false);
    $form->add_input("Numéro de téléphone", "phone_number", "text", false);
    $form->add_input("Avatar", "profile_pic", "file", false);
    $form->add_submit("Modifier");
    $form->end();
    return $form->createView();
  }
}
