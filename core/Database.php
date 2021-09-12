<?php

namespace app\core;

use PDO;
use Dotenv\Dotenv;
use app\models\User;
use app\models\Event;
use app\models\Product;
use app\models\DirectMessage;

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

  // ===== ===== ===== ===== =====
  // User
  // ===== ===== ===== ===== =====

  public function findUserAndRatingById(int $id): ?array
  {
    $columns = "users.id AS id, username, email, role, first_name, last_name, postal_address, city, zip_code, phone_number,
    is_account_active, profile_pic, users.added_at, AVG(score) AS score";
    $sql = "SELECT $columns FROM users
    JOIN ratings ON users.id = rated_id
    WHERE users.id = $id;";
    return $this->db->query($sql)->fetch();
  }

  public function addUser(User $user): bool
  {
    $sql = "INSERT INTO users (username, email, password, role, verification_string)
      VALUES (:username, :email, :password, :role, :verification_string);";
    $statement = $this->db->prepare($sql);
    $statement->bindValue(":username", $user->username, \PDO::PARAM_STR);
    $statement->bindValue(":email", $user->email, \PDO::PARAM_STR);
    $statement->bindValue(":password", $user->password, \PDO::PARAM_STR);
    $statement->bindValue(":role", $user->role, \PDO::PARAM_INT);
    $statement->bindValue(":verification_string", $user->verification_string, \PDO::PARAM_STR);
    return $statement->execute();
  }

  public function activateAccount(string $verification_string): bool
  {
    $is_account_active = 0;
    $sql = "UPDATE users SET is_account_active = ?, verification_string = '' WHERE verification_string = ?;";
    $statement = $this->db->prepare($sql);
    $statement->bindParam(1, $is_account_active, \PDO::PARAM_INT);
    $statement->bindParam(2, $verification_string, \PDO::PARAM_STR);
    return $statement->execute();
  }

  public function updatePassword(int $user_id, string $hashed_password): bool
  {
    $sql = "UPDATE users SET password = ? WHERE id = $user_id;";
    $statement = $this->db->prepare($sql);
    $statement->bindParam(1, $hashed_password, \PDO::PARAM_STR);
    return $statement->execute();
  }

  public function updateContact(int $user_id, array $updated_columns): bool
  {
    if (!$updated_columns)
      return true;
    $column_names = implode(", ", array_keys($updated_columns));
    $values = preg_replace("/(\w+)/", "$1 = ?", $column_names);
    $sql = "UPDATE users SET $values WHERE id = $user_id;";
    $statement = $this->db->prepare($sql);
    $i = 1;
    foreach ($updated_columns as $key => &$value) {
      $statement->bindParam($i, $value, \PDO::PARAM_STR);
      $i++;
    }
    return $statement->execute();
  }

  // ===== ===== ===== ===== =====
  // Product
  // ===== ===== ===== ===== =====

  public function addProduct(Product $product): bool
  {
    $sql = "INSERT INTO products (name, description, price, quantity, seller_id, category_id, image, added_at)
     VALUES (:name, :description, :price, :quantity, :seller_id, :category_id, :image, NOW());";
    $statement = $this->db->prepare($sql);
    $statement->bindParam(":name", $product->getName(), \PDO::PARAM_STR);
    $statement->bindParam(":description", $product->getDescription(), \PDO::PARAM_STR);
    $statement->bindParam(":price", $product->getPrice(), \PDO::PARAM_INT);
    $statement->bindParam(":quantity", $product->getQuantity(), \PDO::PARAM_INT);
    $statement->bindParam(":seller_id", $product->getSellerId(), \PDO::PARAM_INT);
    $statement->bindParam(":category_id", $product->getCategoryId(), \PDO::PARAM_INT);
    $statement->bindParam(":image", $product->getImage(), \PDO::PARAM_STR);
    return $statement->execute();
  }

  public function updateProduct(int $product_id, array $updated_columns): bool
  {
    if (!$updated_columns)
      return true;
    $column_names = implode(", ", array_keys($updated_columns));
    $values = preg_replace("/(\w+)/", "$1 = ?", $column_names);
    $sql = "UPDATE products SET $values WHERE id = $product_id;";
    $statement = $this->db->prepare($sql);
    $i = 1;
    foreach ($updated_columns as $key => &$value) {
      $type = (gettype($value) === "integer") ? \PDO::PARAM_INT : \PDO::PARAM_STR;
      $statement->bindParam($i, $value, $type);
      $i++;
    }
    return $statement->execute();
  }

  public function deleteProduct(int $id): bool
  {
    $sql = "DELETE FROM products WHERE id = ?";
    $statement = $this->db->prepare($sql);
    $statement->bindParam(1, $id, \PDO::PARAM_INT);
    return $statement->execute();
  }

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

  // ===== ===== ===== ===== =====
  // Messages
  // ===== ===== ===== ===== =====

  public function addMessage(DirectMessage $message): bool
  {
    $sql = "INSERT INTO direct_messages (sender_id, recipient_id, subject, content, added_at)
    VALUES (:sender_id, :recipient_id, :subject, :content, NOW());";
    $statement = $this->db->prepare($sql);
    $statement->bindValue(":sender_id", $message->getSenderId(), \PDO::PARAM_INT);
    $statement->bindValue(":recipient_id", $message->getRecipientId(), \PDO::PARAM_INT);
    $statement->bindValue(":subject", $message->getSubject(), \PDO::PARAM_STR);
    $statement->bindValue(":content", $message->getContent(), \PDO::PARAM_STR);
    return $statement->execute();
  }

  // ===== ===== ===== ===== =====
  // Ratings
  // ===== ===== ===== ===== =====

  public function addRating(int $rated_id, int $rater_id, int $score): bool
  {
    $where = "WHERE rated_id = $rated_id AND rater_id = $rater_id";
    $query = $this->db->query("SELECT * FROM ratings $where;");
    $query = $query->fetch();

    if (!$query)
      $sql = "INSERT INTO ratings (rated_id, rater_id, score, added_at) VALUES (?, ?, ?, NOW());";
    else
      $sql = "UPDATE ratings SET rated_id = ?, rater_id = ?, score = ?, added_at = NOW() $where";

    $statement = $this->db->prepare($sql);
    $statement->bindParam(1, $rated_id, \PDO::PARAM_INT);
    $statement->bindParam(2, $rater_id, \PDO::PARAM_INT);
    $statement->bindParam(3, $score, \PDO::PARAM_INT);
    return $statement->execute();
  }

  // ===== ===== ===== ===== =====
  // Events
  // ===== ===== ===== ===== =====

  public function addEvent(Event $event): bool
  {
    $columns = "name, description, author_id, start_date, end_date";
    if ($event->getImage())
      $columns .= ", image";
    $placeholders = preg_replace("/(\w+)/", ":$1", $columns);
    $sql = "INSERT INTO events ($columns, added_at) VALUES ($placeholders, NOW())";
    $statement = $this->db->prepare($sql);
    $statement->bindValue(":name", $event->getName(), \PDO::PARAM_STR);
    $statement->bindValue(":description", $event->getDescription(), \PDO::PARAM_STR);
    $statement->bindValue(":author_id", $event->getAuthorId(), \PDO::PARAM_INT);
    $statement->bindValue(":start_date", $event->getStartDate(), \PDO::PARAM_STR);
    $statement->bindValue(":end_date", $event->getEndDate(), \PDO::PARAM_STR);
    if ($event->getImage())
      $statement->bindValue(":image", $event->getImage(), \PDO::PARAM_STR);

    return $statement->execute();
  }

  public function updateEvent(int $eventId, array $updatedColumns): bool
  {
    if (!$updatedColumns)
      return true;
    $columnNames = implode(", ", array_keys($updatedColumns));
    $values = preg_replace("/(\w+)/", "$1 = ?", $columnNames);
    $sql = "UPDATE events SET $values WHERE id = $eventId;";
    $statement = $this->db->prepare($sql);
    $i = 1;
    foreach ($updatedColumns as $key => &$value) {
      $type = (gettype($value) === "integer") ? \PDO::PARAM_INT : \PDO::PARAM_STR;
      $statement->bindParam($i, $value, $type);
      $i++;
    }
    return $statement->execute();
  }

  public function deleteEvent(int $eventId): bool
  {
    $sql = "DELETE FROM events WHERE id = ?;";
    $statement = $this->db->prepare($sql);
    $statement->bindParam(1, $eventId, \PDO::PARAM_INT);
    return $statement->execute();
  }
}
