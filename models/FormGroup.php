<?php

namespace app\models;

class FormGroup
{
  public $labelText;
  public $forNameId;
  public $inputType;
  public $maxLength;
  public $isRequired;

  public function __construct($labelText, $forNameId, $inputType, $maxLength = null, $isRequired = "true")
  {
    $this->labelText = $labelText;
    $this->forNameId = $forNameId;
    $this->inputType = $inputType;
    if ($maxLength)
      $this->maxLength = $maxLength;
    $this->isRequired = $isRequired;
  }
}