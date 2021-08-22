<?php

namespace app\core;

use app\models\Product;

class Session
{
  public function __construct()
  {
    session_start();
  }

  public function getUser()
  {
    return (!$this->hasUser()) ? null : $_SESSION["user"];
  }

  public function setUser($user)
  {
    $_SESSION["user"] = $user;

    return $this;
  }

  public function hasUser()
  {
    return array_key_exists("user", $_SESSION);
  }

  public function removeUser()
  {
    unset($_SESSION["user"]);
  }

  public function updateProducts()
  {
    $user = $this->getUser();
    if (!$user)
      return;
    $user["products"] = Application::$instance
      ->database
      ->findAll(Product::DB_TABLE, ["*"], ["seller_id" => $user["id"]]);
    $this->setUser($user);
  }
}