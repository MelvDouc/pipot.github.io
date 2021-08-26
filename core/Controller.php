<?php

namespace app\core;

use app\models\Form;
use app\models\User;
use app\models\Product;

class Controller
{
  public function render(string $page, array $params = []): void
  {
    Application::$instance->router->renderView($page, $params);
  }

  protected function redirect(string $location, string $page, array $params = [])
  {
    header("Location: $location");
    return $this->render($page, $params);
  }

  protected function redirectHome(array $params = [])
  {
    return $this->redirect("/accueil", "home", $params);
  }

  protected function redirectToLogin()
  {
    return $this->redirect("/connexion", "authentication/login", [
      "error" => "Vous n'êtes pas connecté."
    ]);
  }

  protected function redirectNotFound(array $params = [])
  {
    return $this->render("404", $params);
  }

  protected function hasSessionUser(): bool
  {
    return Application::$instance->session->hasUser();
  }

  protected function getSessionUser(): array | false
  {
    return Application::$instance->session->getUser();
  }

  protected function isLoggedAsUser(): bool
  {
    return $this->hasSessionUser()
      && (int)$this->getSessionUser()["role"] === User::ROLES["USER"];
  }

  protected function isLoggedAsAdmin(): bool
  {
    return $this->hasSessionUser()
      && (int)$this->getSessionUser()["role"] === User::ROLES["ADMIN"];
  }

  protected function findProductById(int $id): array | false
  {
    return Application::$instance
      ->database
      ->findProductById($id);
  }

  private function getCategoryOptions(): array
  {
    $categories = Application::$instance
      ->database
      ->findAll("categories", ["id", "name"]);
    $category_options = [];
    foreach ($categories as $category)
      $category_options[$category["id"]] = ucfirst($category["name"]);
    return $category_options;
  }

  protected function getAddForm(): Form
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
    return $form;
  }

  protected function getUpdateForm(string $action, array $product): Form
  {
    $form = new Form();
    $form->start($action, true);
    $form->add_input("Nom", "name", "text", true, [
      Product::NAME_MAX_LENGTH,
      "value" => $product["name"]
    ]);
    $form->add_textarea("Description", "description", true, $product["description"]);
    $form->add_input("Prix en Pipots", "price", "number", true, [
      "value" => $product["price"]
    ]);
    $form->add_input("Quantité disponible", "quantity", "number", true, [
      "value" => $product["quantity"]
    ]);
    $form->add_input("Image", "image", "file", false);
    $form->add_checkbox("Utiliser l'image par défaut", "delete_image");
    $form->add_select("Catégorie", "category_id", $this->getCategoryOptions(), $product["category_id"]);
    $form->add_submit("Modifier");
    $form->end();
    return $form;
  }
}
