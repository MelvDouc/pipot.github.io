<?php

namespace app\controllers;

use app\core\Controller;
use app\core\Application;
use app\models\Basket;
use app\models\Product;

class BasketController extends Controller
{
  public function my_basket()
  {
    if (!$this->hasSessionUser())
      return $this->redirectToLogin();

    $user = $this->getSessionUser();
    $user["basket"] = Application::$instance
      ->database
      ->getBasket((int)$user["id"]);

    return $this->render("user/my-basket", [
      "user" => $user,
    ]);    
  }

  public function add()
  {
    $id = $this->getParamId();
    if (!$id)
      return $this->redirectNotFound();

    if (!$this->hasSessionUser())
      return $this->redirectToLogin();

    $product = Application::$instance
      ->database
      ->findProductById($id);

    if (!$product)
      return $this->redirect("/mon-panier", "user/my-basket", [
        "error_message" => "L'article n'existe pas."
      ]);

    $user = $this->getSessionUser();
    $add = Application::$instance
      ->database
      ->addToBasket($product, (int)$user["id"]);
    
    if (!$add)
      return $this->redirect("/mon-panier", "user/my-basket", [
        "error_message" => "Une erreur s'est produite lors de l'ajout de l'article au panier."
      ]);

    $user["basket"] = Application::$instance
      ->database
      ->getBasket((int)$user["id"]);

    return $this->render("user/my-basket", [
      "user" => $user,
      "success_message" => "L'article a bien été ajouté au panier."
    ]);
  }

  public function delete()
  {
    $id = $this->getParamId();
    if (!$id)
      return $this->redirectNotFound();

    if (!$this->hasSessionUser())
      return $this->redirectToLogin();
    
    $user = $this->getSessionUser();
    $basket = new Basket((int)$user["id"]);

    if (!$basket->has($id))
      return $this->redirect("/mon-panier", "user/my-basket", [
        "error_message" => "Cet article n'est pas dans votre panier."
      ]);
    
    $deletion = Application::$instance
      ->database
      ->deleteFromBasket($id);

    if (!$deletion)
      return $this->redirect("/mon-panier", "user/my-basket", [
        "error_message" => "Une erreur s'est produite lors de la suppression de l'article."
      ]);

    return $this->redirect("/mon-panier", "user/my-basket", [
      "success_message" => "L'article a bien été supprimé du panier."
    ]);
  }
}