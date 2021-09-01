<?php

namespace app\models\forms\products;

use app\models\Product;
use app\models\forms\Form;

class UpdateProductForm extends Form
{
  private string $action;
  private array $product;

  public function __construct(string $action, array $product)
  {
    $this->action = $action;
    $this->product = $product;
  }

  public function createView(): string
  {
    $form = new Form();
    $form->start($this->action, true);
    $form->add_input("Nom", "name", "text", true, [
      Product::NAME_MAX_LENGTH,
      "value" => $this->product["name"]
    ]);
    $form->add_textarea("Description", "description", true, $this->product["description"]);
    $form->add_input("Prix en Pipots", "price", "number", true, [
      "value" => $this->product["price"]
    ]);
    $form->add_input("Quantité disponible", "quantity", "number", true, [
      "value" => $this->product["quantity"]
    ]);
    $form->add_input("Image", "image", "file", false);
    $form->add_checkbox("Utiliser l'image par défaut", "delete_image");
    $form->add_select("Catégorie", "category_id", $this->getCategoryOptions(), $this->product["category_id"]);
    $form->add_submit("Modifier");
    $form->end();
    return $form->createView();
  }
}
