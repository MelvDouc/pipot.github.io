<?php

namespace app\admin;

use app\core\Application;
use app\core\Request;
use app\models\Event;
use app\models\forms\events\AddEventForm;
use app\models\forms\events\UpdateEventForm;

class EventController extends AdminController
{
  public function add(Request $request)
  {
    if (!$this->isLoggedAsAdmin())
      return $this->redirectHome();

    $form = new AddEventForm();

    if ($request->isGet())
      return $this->render("admin/events/add", [
        "form" => $form->createView()
      ]);

    $userId = (int) $this->getSessionUser()["id"];
    $event = new Event($request->getBody(), $userId);
    $validation = $event->validate();

    if ($validation !== 1)
      return $this->render("admin/events/add", [
        "form" => $form->createView(),
        "error_message" => $validation
      ]);

    if (!$event->save())
      return $this->render("admin/events/add", [
        "form" => $form->createView(),
        "error_message" => "L'ajout de l'événement a échoué."
      ]);

    return $this->redirect("/evenements");
  }

  public function update(Request $request)
  {
    if (!$this->isLoggedAsAdmin())
      return $this->redirectHome();

    if (!($id = $request->getParamId()))
      return $this->redirectNotFound();

    $event = Application::$instance
      ->database
      ->findOne(Event::DB_TABLE, ["*"], ["id" => $id]);
    if (!$event) return $this->redirectNotFound();

    $form = new UpdateEventForm($event);
    $params = [
      "form" => $form->createView(),
      "event_id" => $event["id"],
      "event_name" => $event["name"]
    ];

    if ($request->isGet())
      return $this->render("admin/events/update", $params);

    $updatedEvent = new Event($request->getBody(), (int) $event["author_id"]);

    if (!($validation = $updatedEvent->validate())) {
      $params["error_message"] = $validation;
      return $this->render("admin/events/update", $params);
    }

    if (!$updatedEvent->updateFrom($event)) {
      $params["error_message"] = "La modification de l'événement a échoué.";
      return $this->render("admin/events/update", $params);
    }

    return $this->redirect("/evenements");
  }

  public function delete(Request $request)
  {
    if (!$request->isPost())
      return $this->redirectNotFound();

    if (!$this->isLoggedAsAdmin())
      return $this->redirectHome();

    if (!($id = $request->getParamId()))
      return $this->redirectNotFound();

    $event = Application::$instance
      ->database
      ->findOne(Event::DB_TABLE, ["*"], ["id" => $id]);
    if (!$event) return $this->redirectNotFound();

    if (!Application::$instance->database->deleteEvent($id))
      return $this->redirect("/evenement/$id");

    return $this->redirect("/evenements");
  }
}
