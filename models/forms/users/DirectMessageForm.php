<?php

namespace app\models\forms\users;

use app\models\forms\Form;

class DirectMessageForm extends Form
{
  public function createView(): string
  {
    $form = new Form();
    $form->start("/messagerie", false);
    $form->add_input("Destinataire", "recipient", "text", true, ["maxlength" => 50]);
    $form->add_input("Sujet", "subject", "text", true, ["maxlength" => 50]);
    $form->add_textarea("Message", "content", true);
    $form->add_submit("Envoyer");
    $form->end();
    return $form->createView();
  }
}
