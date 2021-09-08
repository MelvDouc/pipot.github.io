<?php

namespace app\models\forms\events;

use app\models\Event;
use app\models\forms\Form;

class UpdateEventForm extends Form
{
  private array $event;

  public function __construct(array $event)
  {
    $this->event = $event;
  }

  public function createView(): string
  {
    $id = $this->event["id"];
    $form = new Form();
    $form->start("/admin-modifier-evenement/$id", true);
    $form->add_input("Nom", "name", "text", true, [
      "maxlength" => Event::NAME_MAX_LENGTH,
      "placeholder" => $this->event["name"]
    ]);
    $form->add_textarea("Description", "description", true, $this->event["description"]);
    $form->add_input("Date de début", "start_date", "datetime-local", true, [
      "value" => $this->getDatePlaceholder("start_date")
    ]);
    $form->add_input("Date de fin", "end_date", "datetime-local", true, [
      "value" => $this->getDatePlaceholder("end_date")
    ]);
    $form->add_input("Image", "image", "file", false);
    $form->add_submit("Modifier");
    $form->end();
    return $form->createView();
  }

  private function getDatePlaceholder(string $dateKey)
  {
    return date("Y-m-d\TH:i", strtotime($this->event[$dateKey]));
  }
}
