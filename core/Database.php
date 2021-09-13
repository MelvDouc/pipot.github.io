<?php

namespace app\core;

use PDO;
use Dotenv\Dotenv;
use app\models\User;

class Database
{
  private PDO $db;
  private string $host;
  private string $db_name;
  private string $user;
  private string $password;

  public function __construct()
  {
    $dotenv = Dotenv::createImmutable(Application::$ROOT_DIR);
    $dotenv->load();
    $this->host = $_ENV["DB_HOST"];
    $this->db_name = $_ENV["DB_NAME"];
    $this->user = $_ENV["DB_USER"];
    $this->password = $_ENV["DB_PASSWORD"];
  }

  public function connect(): void
  {
    try {
      $this->db = new \PDO(
        "mysql:host=$this->host;dbname=$this->db_name;charset=utf8",
        $this->user,
        $this->password
      );
      $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    } catch (\PDOException $e) {
      echo $e->getMessage();
    }
  }

  // ===== ===== ===== ===== =====
  // General
  // ===== ===== ===== ===== =====

  public function valueExists(string $table, string $column, string | int $value): bool
  {
    if (gettype($value) === "string")
      $value = "'" . $value . "'";
    $sql = "SELECT * FROM $table WHERE $column = $value;";
    return (bool)($this->db->query($sql)->fetch());
  }

  private function formatSearchQuery(string $table, array $columns = ["*"], array $where = [], string $connector = "AND"): string
  {
    $columns = implode(",", $columns);
    $sql = "SELECT $columns FROM $table";

    if (!$where) return $sql;
    $sql .= " WHERE ";

    $search = [];
    foreach ($where as $column => $value) {
      if (is_string($value))
        $value = "'" . $value . "'";
      $search[] = "$column = $value";
    }
    $search = implode(" $connector ", $search);
    $sql .= $search;

    return $sql;
  }

  public function findOne(string $table, array $columns = ["*"], array $where = [], string $connector = "AND"): array | false
  {
    $sql = $this->formatSearchQuery($table, $columns, $where, $connector);
    return $this->db->query($sql)->fetch();
  }

  public function findAll(string $table, array $columns = ["*"], array $where = [], string $connector = "AND"): array | false
  {
    $sql = $this->formatSearchQuery($table, $columns, $where, $connector);
    return $this->db->query($sql)->fetchAll();
  }

  public function add(string $table, array $values): bool
  {
    $keys = array_keys($values);
    $columns = implode(", ", $keys);
    $placeholdersArray = array_fill(0, count($keys), "?");
    $placeholders = implode(", ", $placeholdersArray);
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders);";
    $statement = $this->db->prepare($sql);
    $i = 1;
    foreach ($values as $value) {
      $statement->bindValue($i, $value);
      $i++;
    }
    return $statement->execute();
  }

  public function update(string $table, array $values, int $id): bool
  {
    $placeholdersArray = array_map(fn ($key) => "$key = ?", array_keys($values));
    $placeholders = implode(", ", $placeholdersArray);
    $sql = "UPDATE $table SET $placeholders WHERE id = $id;";
    $statement = $this->db->prepare($sql);
    $i = 1;
    foreach ($values as $value) {
      $statement->bindValue($i, $value);
      $i++;
    }
    return $statement->execute();
  }

  public function delete(string $table, int $id): bool
  {
    $sql = "DELETE FROM $table WHERE id = ?;";
    $statement = $this->db->prepare($sql);
    $statement->bindValue(1, $id);
    return $statement->execute();
  }

  // ===== ===== ===== ===== =====
  // Ratings
  // ===== ===== ===== ===== =====

  public function findAverageScore(int $rated_id): ?float
  {
    $alias = "average_score";
    $sql = "SELECT AVG(score) AS $alias FROM ratings WHERE rated_id = $rated_id;";
    return $this->db->query($sql)->fetch()[$alias];
  }

  // ===== ===== ===== ===== =====
  // Product
  // ===== ===== ===== ===== =====

  public function findProductById(int $id): array | false
  {
    $sql = "SELECT
    products.id AS id, products.name, products.description, products.price,
    products.quantity, products.seller_id, users.username AS seller, products.category_id,
    products.image, categories.name AS category, products.added_at
    FROM `products`
    JOIN users ON seller_id = users.id
    JOIN categories ON category_id = categories.id
    WHERE products.id = $id
    ORDER BY added_at DESC;";
    return $this->db->query($sql)->fetch();
  }

  public function findAllProducts(): array | false
  {
    $sql = "SELECT products.id, products.name, products.description, price, quantity, seller_id,
    users.username AS seller, category_id, categories.name AS category, image, products.added_at
    FROM products
    JOIN users ON users.id = seller_id
    JOIN categories ON categories.id = category_id
    ORDER BY products.id";
    return $this->db->query($sql)->fetchAll();
  }

  public function findProductsByUserId(int $id): array | false
  {
    $sql = "SELECT
    products.id, products.name, products.description, price, quantity, seller_id,
    category_id, categories.name AS category, image, products.added_at
    FROM `products`
    JOIN categories ON category_id = categories.id
    WHERE seller_id = $id;";
    return $this->db->query($sql)->fetchAll();
  }

  public function findCategoryProducts(int $category_id): array | false
  {
    $sql = "SELECT
    products.id AS id,
    products.name AS name,
    description,
    price,
    quantity,
    seller_id,
    users.username AS seller,
    image,
    added_at
    FROM products
    JOIN users ON users.id = seller_id
    WHERE category_id = $category_id
    ORDER BY added_at DESC;";
    return $this->db->query($sql)->fetchAll();
  }

  public function findProductByKeywords(string $keywords): ?array
  {
    $array = explode(" ", $keywords);
    $result = [];
    foreach ($array as $keyword) {
      $sql = "SELECT products.id, products.name, products.description, price, quantity, seller_id,
      users.username AS seller, category_id, categories.name AS category, image, products.added_at
      FROM products
      JOIN users ON users.id = seller_id
      JOIN categories ON categories.id = category_id
      WHERE products.name LIKE '%$keyword%' OR products.description LIKE '%$keyword%';";
      $products = $this->db->query($sql)->fetchAll();
      if (!$products) continue;
      foreach ($products as $product)
        if (!array_key_exists($product["id"], $result))
          $result[$product["id"]] = $product;
    }
    return $result;
  }

  // ===== ===== ===== ===== =====
  // Basket
  // ===== ===== ===== ===== =====

  public function getBasket(int $user_id): array | false
  {
    $columns = "basket.id AS basket_id, product_id, products.name AS name, products.description,
    price, quantity, image, products.category_id, categories.name AS category,
    basket.seller_id, users.username AS seller_username, products.added_at";
    $sql = "SELECT $columns
    FROM basket
    JOIN products ON basket.product_id = products.id
    JOIN users ON basket.seller_id = users.id
    JOIN categories ON products.category_id = categories.id
    WHERE buyer_id = $user_id;";
    return $this->db->query($sql)->fetchAll();
  }

  public function addToBasket(array $product, int $buyer_id): bool
  {
    $product_id = (int)$product["id"];
    $seller_id = (int)$product["seller_id"];
    $sql = "INSERT INTO basket (product_id, seller_id, buyer_id, added_at)
      VALUES (:product_id, :seller_id, :buyer_id, NOW());";
    $statement = $this->db->prepare($sql);
    $statement->bindParam(":product_id", $product_id, \PDO::PARAM_INT);
    $statement->bindParam(":seller_id", $seller_id, \PDO::PARAM_INT);
    $statement->bindParam(":buyer_id", $buyer_id, \PDO::PARAM_INT);
    return $statement->execute();
  }

  public function deleteFromBasket(int $basket_id): bool
  {
    $sql = "DELETE FROM basket WHERE id = ?;";
    $statement = $this->db->prepare($sql);
    $statement->bindParam(1, $basket_id, \PDO::PARAM_INT);
    return $statement->execute();
  }
}
