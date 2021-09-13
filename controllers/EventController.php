<?php

namespace app\controllers;

use app\core\Controller;
use app\core\Request;
use app\models\Event;

class EventController extends Controller
{
  public function index(Request $request)
  {
    if (!($id = $request->getParamId()))
      return $this->render("events/all", [
        "title" => "Événements",
        "events" => Event::findAll()
      ]);

    if (!($event = Event::findOne(["id" => $id])))
      return $this->redirectNotFound();

    return $this->render("events/single", [
      "title" => $event->name,
      "event" => $event,
      "isUserAdmin" => $this->isLoggedAsAdmin()
    ]);
  }
}
