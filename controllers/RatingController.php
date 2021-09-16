<?php

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\Request;
use app\models\User;

class RatingController extends Controller
{
  public function rate(Request $req)
  {
    if (!($rated_id = $req->getParamId()))
      return $this->redirectNotFound();

    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    $rated_user = Application::$instance
      ->database
      ->findOne(User::DB_TABLE, ["*"], ["id" => $rated_id]);

    if (!$rated_user) return $this->redirectNotFound();

    if (!($score = (int) $req->get("score")) || $score < 0 || $score > 5)
      return $this->redirectNotFound();

    Application::$instance
      ->database
      ->add(
        "ratings",
        [
          "score" => $score,
          "rated_id" => $rated_id,
          "rater_id" => $user->id,
        ]
      );

    return $this->redirect("/profil/$rated_id");
  }
}
