<?php

namespace app\controllers;

use app\core\Controller;

class SiteController extends Controller
{
  public function home()
  {
    return $this->render("home/home", [
      "title" => "Accueil",
      "flashSuccess" => $this->getFlash("success"),
      "flashErrors" => $this->getFlash("errors")
    ]);
  }
}
