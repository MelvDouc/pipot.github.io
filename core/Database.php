<?php

namespace app\core;

use app\models\Product;
use app\models\User;
use PDO;
use Dotenv\Dotenv;

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
      $this->db = new \PDO("mysql:host=$this->host;dbname=$this->db_name;charset=utf8", $this->user, $this->password);
      $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    } catch (\PDOException $e) {
      echo $e->getMessage();
    }
  }

  public function addUser(User $user): bool
  {
    $hashedPassword = password_hash($user->plain_password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, email, password, role, verification_string, is_account_active, register_date)
      VALUES (:username, :email, :password, :role, :verification_string, 0, NOW());";
    $statement = $this->db->prepare($sql);
    $statement->bindParam(":username", $user->username, \PDO::PARAM_STR);
    $statement->bindParam(":email", $user->email, \PDO::PARAM_STR);
    $statement->bindParam(":password", $hashedPassword, \PDO::PARAM_STR);
    $statement->bindParam(":role", $user->role, \PDO::PARAM_INT);
    $statement->bindParam(":verification_string", $user->verification_string, \PDO::PARAM_STR);
    return $statement->execute();
  }

  public function addProduct(Product $product): bool
  {
    $sql = "INSERT INTO products (name, description, price, quantity, seller_id, category_id, image, creation_date)
     VALUES (:name, :description, :price, :quantity, :seller_id, :category_id, :image, NOW());";
    $statement = $this->db->prepare($sql);
    $statement->bindParam(":name", $product->name, \PDO::PARAM_STR);
    $statement->bindParam(":description", $product->description, \PDO::PARAM_STR);
    $statement->bindParam(":price", $product->price, \PDO::PARAM_INT);
    $statement->bindParam(":quantity", $product->quantity, \PDO::PARAM_INT);
    $statement->bindParam(":seller_id", $product->seller_id, \PDO::PARAM_INT);
    $statement->bindParam(":category_id", $product->category_id, \PDO::PARAM_INT);
    $statement->bindParam(":image", $product->image, \PDO::PARAM_STR);
    return $statement->execute();
  }

  public function getBasket(int $user_id): array | false
  {
    $columns = "cart.id AS cart_id, product_id, products.name AS name, products.description, price, quantity, image, cart.category_id,
    categories.name AS category, cart.seller_id, users.username AS seller_username, creation_date";
    $sql = "SELECT $columns
    FROM cart
    JOIN products ON cart.product_id = products.id
    JOIN users ON cart.seller_id = users.id
    JOIN categories ON cart.category_id = categories.id
    WHERE buyer_id = $user_id;";
    return $this->db->query($sql)->fetchAll();
  }

  public function addToBasket(array $product, int $buyer_id): bool
  {
    $product_id = (int)$product["id"];
    $category_id = (int)$product["category_id"];
    $seller_id = (int)$product["seller_id"];
    $sql = "INSERT INTO cart (product_id, category_id, seller_id, buyer_id, added_at)
      VALUES (:product_id, :category_id, :seller_id, :buyer_id, NOW());";
    $statement = $this->db->prepare($sql);
    $statement->bindParam(":product_id", $product_id, \PDO::PARAM_INT);
    $statement->bindParam(":category_id", $category_id, \PDO::PARAM_INT);
    $statement->bindParam(":seller_id", $seller_id, \PDO::PARAM_INT);
    $statement->bindParam(":buyer_id", $buyer_id, \PDO::PARAM_INT);
    return $statement->execute();
  }

  public function deleteFromBasket(int $cart_id): bool
  {
    $sql = "DELETE FROM cart WHERE id = $cart_id;";
    $statement = $this->db->prepare($sql);
    return $statement->execute();
  }

  private function formatSearchQuery(string $table, array $columns = ["*"], array $where = [], string $connector = "AND"): string
  {
    $columns = implode(",", $columns);
    $sql = "SELECT $columns FROM $table";

    if (!$where)
      return $sql;

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

  public function findProductById(int $id): array | false
  {
    $sql = "SELECT
    products.id AS id, products.name, products.description, products.price,
    products.quantity, products.seller_id, users.username AS seller, products.category_id,
    products.image, categories.name AS category, products.creation_date
    FROM `products`
    JOIN users ON seller_id = users.id
    JOIN categories ON category_id = categories.id
    WHERE products.id = $id;";
    return $this->db->query($sql)->fetch();
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
    creation_date
    FROM products
    JOIN users ON users.id = seller_id
    WHERE category_id = $category_id
    ORDER BY creation_date DESC;";
    return $this->db->query($sql)->fetchAll();
  }

  public function valueExists(string $table, string $column, string | int $value): bool
  {
    if (gettype($value) === "string")
      $value = "'" . $value . "'";
    $sql = "SELECT * FROM $table WHERE $column = $value;";
    return (bool)($this->db->query($sql)->fetch());
  }

  public function activateAccount(string $verification_string): bool
  {
    $sql = "UPDATE users SET is_account_active = 1, verification_string = '' WHERE verification_string = ?;";
    $statement = $this->db->prepare($sql);
    $statement->bindParam(1, $verification_string, \PDO::PARAM_STR);
    return $statement->execute();
  }
}
