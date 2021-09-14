<?php

namespace app\core;

use app\models\User;

class Session
{
  public function __construct()
  {
    session_start();
  }

  public function getUserId(): ?int
  {
    return $_SESSION["user-id"] ?? null;
  }

  public function setUserId(int $userId): void
  {
    $_SESSION["user-id"] = $userId;
  }

  public function hasUser(): bool
  {
    return array_key_exists("user-id", $_SESSION);
  }

  public function removeUserId(): void
  {
    unset($_SESSION["user-id"]);
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
