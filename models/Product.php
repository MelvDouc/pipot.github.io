<?php

namespace app\models;

class Product
{
  public const DB_TABLE = "products";
  private string $name;
  private string $description;
  private int $price;
  private int $seller_id;
  private int $category_id;
  private string $image;
}