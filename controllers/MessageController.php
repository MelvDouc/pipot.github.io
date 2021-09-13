<?php

namespace app\controllers;

use app\core\Application;
use app\core\Request;
use app\core\Controller;
use app\models\DirectMessage;
use app\models\User;

class MessageController extends Controller
{
  public function my_messages(Request $request)
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    if ($request->isGet())
      return $this->get();

    $body = $request->getBody();
    $directMessage = new DirectMessage();
    $directMessage->sender_id = $user->id;
    $directMessage->recipient_id = (int) $body["recipient_id"] ?? null;
    $directMessage->subject = $body["subject"] ?? null;
    $directMessage->content = $body["content"] ?? null;

    if (!$directMessage->isValid())
      return $this->get($directMessage->getErrors());

    return $this->get([], "Le message a bien été envoyé.");
  }

  private function get(?array $errors = [], ?string $success = null)
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    $userId = $user->id;
    $sent_messages = DirectMessage::findAll(["sender_id" => $userId]);
    $received_messages = DirectMessage::findAll(["recipient_id" => $userId]);
    $users = Application::$instance->database->findAll(User::DB_TABLE, ["id", "username"]);

    return $this->render("uer/my-messages", [
      "sent_messages" => $sent_messages,
      "received_messages" => $received_messages,
      "users" => $users,
      "flashErrors" => $errors,
      "flashSuccess" => $success
    ]);
  }
}
