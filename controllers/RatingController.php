<?php

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\Request;
use app\models\User;

class RatingController extends Controller
{
  public function rate(Request $request)
  {
    if (!($rated_id = $request->getParamId()))
      return $this->redirectNotFound();

    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    $rated_user = Application::$instance
      ->database
      ->findOne(User::DB_TABLE, ["*"], ["id" => $rated_id]);

    if (!$rated_user) return $this->redirectNotFound();

    $body = $request->getBody();
    $score = $body["score"] ?? null;

    if (!$score || (int) $score < 0 || (int) $score > 5)
      return $this->redirectNotFound();

    $rater_id = (int) $user["id"];
    Application::$instance
      ->database
      ->addRating($rated_id, $rater_id, (int) $score);

    return $this->redirect("/profil/$rated_id", "user/profile");
  }
}
