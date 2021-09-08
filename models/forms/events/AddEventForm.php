<?php

namespace app\models\forms\events;

use app\models\forms\Form;

class AddEventForm extends Form
{
  public function createView(): string
  {
    $form = new Form();
    $form->start("/admin-ajouter-evenement", true);
    $form->add_input("Nom", "name", "text", true, ["maxlength" => 50]);
    $form->add_textarea("Description", "description");
    $form->add_input("Date et heure de début", "start_date", "datetime-local");
    $form->add_input("Date et heure de fin", "end_date", "datetime-local");
    $form->add_input("Image", "image", "file", false);
    $form->add_submit();
    $form->end();
    return $form->createView();
  }
}
