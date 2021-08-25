<?php

namespace app\core;

class Request
{

  public function getPath()
  {
    return explode("/", $_SERVER["REQUEST_URI"])[1];
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

  public function getBody(): array
  {
    $body = [];
    $super_global = ($this->isPost()) ? $_POST : $_GET;
    $input = ($this->isPost()) ? INPUT_POST : INPUT_GET;
    foreach ($super_global as $key => $_)
      $body[$key] = filter_input($input, $key, FILTER_SANITIZE_SPECIAL_CHARS);
    return $body;
  }
}
