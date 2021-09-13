<?php

namespace app\admin;

use app\core\Application;
use app\core\Request;
use app\models\Event;

class EventController extends AdminController
{
  public function add(Request $request)
  {
    if (!$this->isLoggedAsAdmin())
      return $this->redirectHome();

    if ($request->isGet())
      return $this->render("admin/events/add", [
        "title" => "Ajouter un événement"
      ]);

    $userId = (int) $this->getSessionUser()["id"];
    $body = $request->getBody();
    $event = new Event($request->getBody(), $userId);
    $event->name = $body["name"] ?? null;
    $event->description = $body["description"] ?? null;
    $event->author_id = $userId;
    $event->start_date = $body["start_date"] ?? null;
    $event->end_date = $body["end_date"] ?? null;
    $event->setFile($_FILES["image"] ?? null);

    if (!$event->isValid()) {
      return $this->render("admin/events/add", [
        "title" => "Ajouter un événement",
        "flashErrors" => $event->getErrors()
      ]);
    }

    $event->save();
    return $this->redirect("/evenements");
  }

  public function update(Request $request)
  {
    if (!$this->isLoggedAsAdmin())
      return $this->redirectHome();

    if (!($id = $request->getParamId()))
      return $this->redirectNotFound();

    if (!($event = Event::findOne(["id" => $id])))
      return $this->redirectNotFound();

    if ($request->isGet())
      return $this->render("admin/events/update", [
        "title" => "Modifier un événement",
        "event" => $event
      ]);

    $body = $request->getBody();
    $event->name = $body["name"] ?? null;
    $event->description = $body["description"] ?? null;
    $event->start_date = $body["start_date"] ?? null;
    $event->end_date = $body["end_date"] ?? null;
    $event->setFile($_FILES["image"] ?? null);

    if (!$event->isValid()) {
      return $this->render("admin/events/add", [
        "title" => "Ajouter un événement",
        "flashErrors" => $event->getErrors()
      ]);
    }

    $event->update();
    return $this->redirect("/evenements/$id");
  }

  public function delete(Request $request)
  {
    if (!$this->isLoggedAsAdmin())
      return $this->redirectHome();

    if (!($id = $request->getParamId()))
      return $this->redirectNotFound();

    if (!($event = Event::findOne(["id" => $id])))
      return $this->redirectNotFound();

    $event->delete();
    return $this->redirect("/evenements");
  }
}
