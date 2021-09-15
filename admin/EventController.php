<?php

namespace app\admin;

use app\core\Request;
use app\models\Event;

class EventController extends AdminController
{
  public function add(Request $req)
  {
    if (!$this->isLoggedAsAdmin())
      return $this->redirectHome();

    if ($req->isGet())
      return $this->render("admin/events/add", [
        "title" => "Ajouter un événement"
      ]);

    $userId = (int) $this->getSessionUser()["id"];
    $event = new Event();
    $event->name = $req->get("name");
    $event->description = $req->get("body");
    $event->author_id = $userId;
    $event->start_date = $req->get("start_date");
    $event->end_date = $req->get("end_date");
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

  public function update(Request $req)
  {
    if (!$this->isLoggedAsAdmin())
      return $this->redirectHome();

    if (!($id = $req->getParamId()))
      return $this->redirectNotFound();

    if (!($event = Event::findOne(["id" => $id])))
      return $this->redirectNotFound();

    if ($req->isGet())
      return $this->render("admin/events/update", [
        "title" => "Modifier un événement",
        "event" => $event
      ]);

    $event->name = $req->get("name");
    $event->description = $req->get("description");
    $event->start_date = $req->get("start_date");
    $event->end_date = $req->get("end_date");
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
