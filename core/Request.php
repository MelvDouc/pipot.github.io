<?php

namespace app\core;

class Request
{

  public function getPath()
  {
    return preg_replace("/\/\d+$/", "", $_SERVER["REQUEST_URI"]);
  }

  public function getParamId(): int | null
  {
    $match = [];
    preg_match("/\/\d+$/", $_SERVER["REQUEST_URI"], $match);
    if (!$match)
      return null;
    return (int)str_replace("/", "", $match[0]);
  }

  public function getMethod()
  {
    return strtolower($_SERVER["REQUEST_METHOD"]);
  }

  public function isGet()
  {
    return $this->getMethod() === "get";
  }

  public function isPost()
  {
    return $this->getMethod() === "post";
  }

  public function get(string $key): mixed
  {
    if (isset($_POST[$key]))
      return filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
    return null;
  }
}
