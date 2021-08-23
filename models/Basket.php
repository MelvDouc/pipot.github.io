<?php

namespace app\models;

use app\core\Application;

class Basket
{
  private array $products;

  public function __construct(int $user_id)
  {
    $this->products = Application::$instance
      ->database
      ->getBasket($user_id);  
  }

  public function has(int $product_id): bool
  {
    foreach ($this->products as $product)
      if ((int)$product["product_id"] === $product_id)
        return true;
    return false;
  }
}