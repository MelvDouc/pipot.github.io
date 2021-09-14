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

  public function delete(string $table, array $values): bool
  {
    $placeholdersArray = array_map(fn ($col) => "$col = ?", array_keys($values));
    $placeholders = implode(" AND ", $placeholdersArray);
    $sql = "DELETE FROM $table WHERE $placeholders";
    $statement = $this->db->prepare($sql);
    $i = 1;
    foreach ($values as $value) {
      $statement->bindValue($i, $value);
      $i++;
    }
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

  public function findBasket(int $user_id): array | false
  {
    $columnsArray = [
      "basket.id AS basket_id",
      "basket.product_id",
      "basket.seller_id",
      "products.name AS name",
      "products.description",
      "products.price",
      "products.quantity",
      "products.image",
      "products.category_id",
      "products.added_at",
      "categories.name AS category",
      "users.username AS seller",
    ];
    $columns = implode(", ", $columnsArray);
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
