<?php

namespace app\models;

class Form
{
  private string $opening_tag;
  private string $closing_tag;
  private array $fields;

  public function start($action, bool $supports_files, $class = null): void
  {
    $class_attribute = ($class) ? "class='$class'" : "";
    $enctype = ($supports_files) ? "enctype='multipart/form-data'" : "";
    $this->opening_tag = "<form method='POST' action='$action' $enctype $class_attribute>";
  }

  public function end(): void
  {
    $this->closing_tag = "</form>";
  }

  private function stringify_attributes(array $attributes): string
  {
    $string = "";
    foreach ($attributes as $key => $value)
      $string .= "$key='$value' ";
    return $string;
  }

  private function get_label(string $label_text, string $name): string
  {
    return "<label for='$name'>$label_text</label>";
  }

  public function add_input(string $label_text, string $name, string $type = "", bool $is_required = true, array $attributes = []): void
  {
    if (!$type) $type = $name;
    $attributes = $this->stringify_attributes($attributes);
    $required_attribute = ($is_required) ? "required" : "";
    $label = $this->get_label($label_text, $name);
    $input = "<input type='$type' name='$name' id='$name' $required_attribute $attributes />";
    $this->fields[] = "<div class='form-group'>$label $input</div>";
  }

  public function add_textarea(string $label_text, string $name, bool $is_required = true, array $attributes = []): void
  {
    $attributes = $this->stringify_attributes($attributes);
    $required_attribute = ($is_required) ? "required" : "";
    $label = $this->get_label($label_text, $name);
    $textarea = "<textarea name='$name' id='$name' $required_attribute $attributes></textarea>";
    $this->fields[] = "<div class='form-group'>$label $textarea</div>";
  }

  public function add_checkbox(string $label_text, string $name): void
  {
    $label = $this->get_label($label_text, $name);
    $checkbox = "<input type='checkbox' id='$name' name='$name'>";
    $this->fields[] = "<div class='form-checkbox'>$checkbox $label</div>";
  }

  public function add_submit(string $text = "Valider"): void
  {
    $this->fields[] = "<div class='form-submit'>
      <button class='button' type='submit'>$text</button>
    </div>";
  }

  public function createView(): string
  {
    $view = $this->opening_tag;
    foreach ($this->fields as $field)
      $view .= $field;
    $view .= $this->closing_tag;
    return $view;
  }
}
