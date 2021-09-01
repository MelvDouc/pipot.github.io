<?php

namespace app\core;

use app\models\User;

class Session
{
  public function __construct()
  {
    session_start();
  }

  public function getUser(): ?array
  {
    return $_SESSION["user"] ?? null;
  }

  public function setUser(array $user): void
  {
    $_SESSION["user"] = $user;
  }

  public function updateUser(): void
  {
    $user = Application::$instance
      ->database
      ->findOne(User::DB_TABLE, ["*"], ["id" => $this->getUser()["id"]]);
    $this->setUser($user);
  }

  public function hasUser(): bool
  {
    return array_key_exists("user", $_SESSION);
  }

  public function removeUser(): void
  {
    unset($_SESSION["user"]);
  }
}
