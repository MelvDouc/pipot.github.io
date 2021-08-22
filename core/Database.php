<?php

namespace app\core;

use app\models\Product;
use app\models\User;
use PDO;
use Dotenv\Dotenv;

class Database
{
  private PDO $db;
  private $host;
  private $db_name;
  private $user;
  private $password;

  public function __construct()
  {
    $dotenv = Dotenv::createImmutable(Application::$ROOT_DIR);
    $dotenv->load();
    $this->host = $_ENV["DB_HOST"];
    $this->db_name = $_ENV["DB_NAME"];
    $this->user = $_ENV["DB_USER"];
    $this->password = $_ENV["DB_PASSWORD"];
  }

  public function connect()
  {
    try {
      $this->db = new \PDO("mysql:host=$this->host;dbname=$this->db_name;charset=utf8", $this->user, $this->password);
      $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    } catch (\PDOException $e) {
      echo $e->getMessage();
    }
  }

  public function addUser(User $user)
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
    $statement->execute();
  }

  public function addProduct(Product $product)
  {
    $sql = "INSERT INTO products (name, description, price, seller_id, category_id, image, creation_date)
     VALUES (:name, :description, :price, :seller_id, :category_id, :image, NOW());";
    $statement = $this->db->prepare($sql);
    $statement->bindParam(":name", $product->name, \PDO::PARAM_STR);
    $statement->bindParam(":description", $product->description, \PDO::PARAM_STR);
    $statement->bindParam(":price", $product->price, \PDO::PARAM_INT);
    $statement->bindParam(":seller_id", $product->seller_id, \PDO::PARAM_INT);
    $statement->bindParam(":category_id", $product->category_id, \PDO::PARAM_INT);
    $statement->bindParam(":image", $product->image, \PDO::PARAM_STR);
    $statement->execute();
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


  public function findOne(string $table, array $columns = ["*"], array $where = [], string $connector = "AND")
  {
    $sql = $this->formatSearchQuery($table, $columns, $where, $connector);
    return $this->db->query($sql)->fetch();
  }

  public function findAll(string $table, array $columns = ["*"], array $where = [], string $connector = "AND")
  {
    $sql = $this->formatSearchQuery($table, $columns, $where, $connector);
    return $this->db->query($sql)->fetchAll();
  }

  public function valueExists($table, $column, $value)
  {
    $sql = "SELECT * FROM $table WHERE $column = '$value';";
    return (bool)($this->db->query($sql)->fetch());
  }

  public function activateAccount($verification_string)
  {
    $sql = "UPDATE users SET is_account_active = 1, verification_string = '' WHERE verification_string = ?;";
    $statement = $this->db->prepare($sql);
    $statement->bindParam(1, $verification_string, \PDO::PARAM_STR);
    $statement->execute();
    return $statement->rowCount();
  }

  public function isCorrectPassword($uuid, $plainPassword)
  {
    $user = $this->findOne(
      User::DB_TABLE,
      ["*"],
      [
        "username" => $uuid,
        "email" => $uuid,
      ],
      "OR"
    );
    if (!$user)
      return false;
    return password_verify($plainPassword, $user["password"]);
  }
}
