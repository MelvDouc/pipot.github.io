<?php

namespace app\core;

use app\models\Product;

class Session
{
  public function __construct()
  {
    session_start();
  }

  public function getUser(): array | null
  {
    if (!$this->hasUser())
      return null;
    $user = &$_SESSION["user"];
    return $user;
  }

  public function setUser(array $user): Session
  {
    $user["basket"] = Application::$instance
      ->database
      ->getBasket((int)$user["id"]);
    $_SESSION["user"] = $user;

    return $this;
  }

  public function hasUser(): bool
  {
    return array_key_exists("user", $_SESSION);
  }

  public function removeUser(): void
  {
    unset($_SESSION["user"]);
  }

  public function updateProducts(): void
  {
    $user = $this->getUser();
    if (!$user)
      return;
    $user["products"] = Application::$instance
      ->database
      ->findAll(Product::DB_TABLE, ["*"], ["seller_id" => $user["id"]]);
    $this->setUser($user);
  }

  public function updateBasket(): void
  {
    if (!$this->hasUser())
      return;
    $id = (int)$this->getUser()["id"];
    $_SESSION["user"]["basket"] = Application::$instance
      ->database
      ->getBasket($id);
  }

  public function isInBasket(int $cart_id): bool
  {
    foreach ($this->getUser()["basket"] as $product)
      if ((int)$product["cart_id"] === $cart_id)
        return true;
    return false;
  }
}
