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

  public function getBody(): array
  {
    $body = [];
    $isPost = $this->isPost();
    $globalArray = $isPost ? $_POST : $_GET;
    $input = $isPost ? INPUT_POST : INPUT_GET;

    foreach (array_keys($globalArray) as $key)
      $body[$key] = filter_input($input, $key, FILTER_SANITIZE_SPECIAL_CHARS);
    if ($isPost) $body["files"] = $_FILES;

    return $body;
  }
}
