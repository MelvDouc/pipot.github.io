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

  public function hasUser(): bool
  {
    return array_key_exists("user", $_SESSION);
  }

  public function removeUser(): void
  {
    unset($_SESSION["user"]);
  }

  public function getFlash(string $key): string|array|null
  {
    return $_SESSION["flash-$key"] ?? null;
  }

  public function setFlash(string $key, string|array $messages): void
  {
    $_SESSION["flash-$key"] = $messages;
  }

  public function removeFlash(string $key): void
  {
    unset($_SESSION["flash-$key"]);
  }
}
