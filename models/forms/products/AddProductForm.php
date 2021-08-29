<?php

namespace app\models\forms;

use app\models\Product;

class AddProductForm extends Form
{
  public function createView(): string
  {
    $form = new Form();
    $form->start("/ajouter-article", true, "add-product-form");
    $form->add_input("Nom", "name", "text", true, ["maxlength" => Product::NAME_MAX_LENGTH]);
    $form->add_textarea("Description", "description");
    $form->add_input("Prix en Pipots", "price", "number");
    $form->add_input("Quantité", "quantity", "number", true, ["value" => 1]);
    $form->add_input("Image", "image", "file", false);
    $form->add_select("Catégorie", "category_id", $this->getCategoryOptions());
    $form->add_submit("Ajouter");
    $form->end();
    return $form->createView();
  }
}
