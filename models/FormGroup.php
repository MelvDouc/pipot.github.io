<?php

namespace app\models;

class FormGroup
{
  public string $labelText;
  public string $forNameId;
  public string $inputType;
  public int $maxLength;
  public bool $isRequired;

  public function __construct(string $labelText, string $forNameId, string $inputType, int $maxLength = null, bool $isRequired = true)
  {
    $this->labelText = $labelText;
    $this->forNameId = $forNameId;
    $this->inputType = $inputType;
    if ($maxLength)
      $this->maxLength = $maxLength;
    $this->isRequired = ($isRequired === true) ? "true" : "";
  }
}