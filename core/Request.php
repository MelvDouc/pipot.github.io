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
    $id = explode("/", $_SERVER["REQUEST_URI"])[2];
    if (!preg_match("/\d+/", $id))
      return null;
    return (int)$id;
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
    foreach ($super_global as $key => $_)
      $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
    return $body;
  }
}
