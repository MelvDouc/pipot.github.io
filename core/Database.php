<?php

namespace app\core;

use app\models\User;
use PDO;
use Dotenv\Dotenv;

class Database {
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

  public function connect() {
    try {
      $this->db = new \PDO("mysql:host=$this->host;dbname=$this->db_name;charset=utf8", $this->user, $this->password);
      $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    } catch (\PDOException $e) {
      echo $e->getMessage();
    }
  }

  public function addUser($username, $email, $password, $role, $verification_string) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, email, password, role, verification_string, is_account_active, register_date)
      VALUES (:username, :email, :password, :role, :verification_string, 0, NOW());";
    $statement = $this->db->prepare($sql);
    $statement->bindParam(":username", $username, \PDO::PARAM_STR);
    $statement->bindParam(":email", $email, \PDO::PARAM_STR);
    $statement->bindParam(":password", $hashedPassword, \PDO::PARAM_STR);
    $statement->bindParam(":role", $role, \PDO::PARAM_INT);
    $statement->bindParam(":verification_string", $verification_string, \PDO::PARAM_STR);
    $statement->execute();
  }

  private function formatSearchQuery(string $table, array $where = [], string $connector = "AND"): string
  {
    $sql = "SELECT * FROM $table";

    if (!$where)
      return $sql;
    
    $sql .= " WHERE ";
    
    $search = [];
    foreach ($where as $column => $value) {
      if (is_string($value))
        $value = "$value";
      $search[] = "$column = $value";
    }
    $search = implode(" $connector ", $search);
    $sql .= $search;
    
    return $sql;
  }

  public function findOne(string $table, array $where = [], string $connector = "AND")
  {
    $sql = $this->formatSearchQuery($table, $where, $connector);
    return $this->db->query($sql)->fetch();
  }

  public function findAll(string $table, array $where = [], string $connector = "AND")
  {
    $sql = $this->formatSearchQuery($table, $where, $connector);
    return $this->db->query($sql)->fetchAll();
  }

  public function userValueExists($column, $value)
  {
    $sql = "SELECT * FROM users WHERE $column = '$value';";
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
    $user = $this->findOne(User::DB_TABLE, [
      "username" => $uuid,
      "email" => $uuid,
    ], "OR");
    if (!$user)
      return false;
    return password_verify($plainPassword, $user["password"]);
  }
}