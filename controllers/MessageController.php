<?php

namespace app\controllers;

use app\core\Application;
use app\core\Request;
use app\core\Controller;
use app\models\DirectMessage;
use app\models\User;

class MessageController extends Controller
{
  public function myMessages(Request $req)
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    $users = Application::$instance
      ->database
      ->findAll(User::DB_TABLE, ["id", "username"]);

    if ($req->isGet()) {
      return $this->render("user/my-messages", [
        "title" => "Messagerie",
        "sent_messages" => DirectMessage::findAll(["sender_id" => $user->id]),
        "received_messages" => DirectMessage::findAll(["recipient_id" => $user->id]),
        "users" => $users,
        "flashSuccess" => $this->getFlash("success"),
        "flashErrors" => $this->getFlash("errors")
      ]);
    }

    $directMessage = new DirectMessage();
    $directMessage->sender_id = $user->id;
    $directMessage->recipient_id = (int) $req->get("recipient_id");
    $directMessage->subject = $req->get("subject");
    $directMessage->content = $req->get("content");

    if (!$directMessage->isValid()) {
      $this->setFlash("errors", $directMessage->getErrors());
      return $this->redirect("/messagerie");
    }

    $directMessage->save();
    $this->setFlash("success", "Le message a bien été envoyé.");
    return $this->redirect("/messagerie");
  }
}
