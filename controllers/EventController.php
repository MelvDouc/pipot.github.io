<?php

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\Request;
use app\models\Event;

class EventController extends Controller
{
  private function findEvent(int $id): ?array
  {
    return Application::$instance
      ->database
      ->findOne(Event::DB_TABLE, ["*"], ["id" => $id]);
  }

  public function all()
  {
    $events = Application::$instance
      ->database
      ->findAll(Event::DB_TABLE);

    $this->render("events/all", [
      "events" => $events
    ]);
  }

  public function single(Request $request)
  {
    if (!($id = $request->getParamId()))
      return $this->redirectNotFound();

    if (!($event = $this->findEvent($id)))
      return $this->redirectNotFound();

    return $this->render("events/single", [
      "event" => $event,
      "isUserAdmin" => $this->isLoggedAsAdmin()
    ]);
  }
}
