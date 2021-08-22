<?php

namespace app\core;

class Session
{
  public function __construct()
  {
    session_start();
    // $_SESSION["user"] = null;
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
}