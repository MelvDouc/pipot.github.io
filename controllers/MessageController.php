<?php

namespace app\controllers;

use app\core\Application;
use app\core\Request;
use app\core\Controller;
use app\models\DirectMessage;
use app\models\forms\users\DirectMessageForm;

class MessageController extends Controller
{
  private function getSentMessages(int $user_id): ?array
  {
    return Application::$instance
      ->database
      ->findAll("direct_messages", ["*"], ["sender_id" => $user_id]);
  }

  private function getReceivedMessages(int $user_id): ?array
  {
    return Application::$instance
      ->database
      ->findAll("direct_messages", ["*"], ["recipient_id" => $user_id]);
  }

  public function my_messages(Request $request)
  {
    $user = $this->getSessionUser();
    if (!$user) return $this->redirectToLogin();

    $user_id = (int)$user["id"];
    $form = new DirectMessageForm();
    $params = [
      "received_messages" => $this->getReceivedMessages($user_id),
      "sent_messages" => $this->getSentMessages($user_id),
      "form" => $form->createView()
    ];

    if ($request->isGet())
      return $this->render("user/my-messages", $params);

    $new_message = new DirectMessage($user_id, $request->getBody());
    $validation = $new_message->validate();

    if ($validation !== 1)
      $params["error_message"] = $validation;
    else if (!$new_message->send())
      $params["error_message"] = "L'envoi du message a échoué.";
    else {
      $params["success_message"] = "Le message a bien été envoyé.";
      $params["sent_messages"] = $this->getSentMessages($user_id);
    }
    return $this->render("user/my-messages", $params);
  }
}
