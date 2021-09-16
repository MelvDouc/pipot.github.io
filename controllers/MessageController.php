<?php

namespace app\controllers;

use app\core\Application;
use app\core\Request;
use app\core\Controller;
use app\models\DirectMessage;
use app\models\User;

class MessageController extends Controller
{
  public function my_messages(Request $req)
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    if ($req->isGet())
      return $this->get();

    $directMessage = new DirectMessage();
    $directMessage->sender_id = $user->id;
    $directMessage->recipient_id = (int) $req->get("recipient_id");
    $directMessage->subject = $req->get("subject");
    $directMessage->content = $req->get("content");

    if (!$directMessage->isValid())
      return $this->get($directMessage->getErrors());

    return $this->get([], "Le message a bien été envoyé.");
  }

  private function get(?array $errors = [], ?string $success = null)
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    $sent_messages = DirectMessage::findAll(["sender_id" => $user->id]);
    $received_messages = DirectMessage::findAll(["recipient_id" => $user->id]);
    $users = Application::$instance
      ->database
      ->findAll(User::DB_TABLE, ["id", "username"]);

    return $this->render("user/my-messages", [
      "sent_messages" => $sent_messages,
      "received_messages" => $received_messages,
      "users" => $users,
      "flashErrors" => $errors,
      "flashSuccess" => $success
    ]);
  }
}
