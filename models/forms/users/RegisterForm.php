<?php

namespace app\models\forms\users;

use app\models\forms\Form;

class RegisterForm extends Form
{
  public function createView(): string
  {
    $form = new Form();
    $form->start("/inscription", false, "register-form");
    $form->add_input("Nom d'utilisateur", "username", "text");
    $form->add_input("Adresse email", "email");
    $form->add_input("Mot de passe", "password");
    $form->add_input("Confirmer le mot de passe", "confirm_password", "password");
    $form->add_checkbox("Accepter les conditions d'utilisation", "agree_terms");
    $form->add_submit("S'inscrire");
    $form->end();
    return $form->createView();
  }
}
