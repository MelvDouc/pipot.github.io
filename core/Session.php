<?php

namespace app\core;

use app\models\User;

class Session
{
  public function __construct()
  {
    session_start();
  }

  public function getUser(): ?User
  {
    return $_SESSION["user"] ?? null;
  }

  public function setUser(User $user): void
  {
    $_SESSION["user"] = $user;
  }

  public function updateUser(): void
  {
    $currentUserId = (int) $this->getUser()->id;
    $user = User::findOne(["id" => $currentUserId]);
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
