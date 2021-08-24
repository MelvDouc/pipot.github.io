<?php

namespace app\controllers;

use app\core\Request;
use app\core\Controller;
use app\core\Application;

class BasketController extends Controller
{
  private function redirectWithError($error_message)
  {
    return $this->redirect("/mon-panier", "user/my-basket", [
      "error_message" => $error_message,
      "user" => $this->getSessionUser()
    ]);
  }

  private function redirectWithSuccess($success_message)
  {
    return $this->redirect("/mon-panier", "user/my-basket", [
      "error_message" => $success_message,
      "user" => $this->getSessionUser()
    ]);
  }

  public function my_basket()
  {
    if (!$this->hasSessionUser())
      return $this->redirectToLogin();

    $user = $this->getSessionUser();
    Application::$instance->session->updateBasket();

    return $this->render("user/my-basket", [
      "user" => $user,
    ]);
  }

  public function add(Request $request)
  {
    $id = $request->getParamId();
    if (!$id)
      return $this->redirectNotFound();

    if (!$this->hasSessionUser())
      return $this->redirectToLogin();

    $product = Application::$instance
      ->database
      ->findProductById($id);

    if (!$product)
      return $this->redirectWithError("L'article n'existe pas.");

    if (Application::$instance->session->isInBasket($id))
      return $this->redirectWithError("Cet article est déjà dans votre panier.");

    $user = $this->getSessionUser();
    $add = Application::$instance
      ->database
      ->addToBasket($product, (int)$user["id"]);

    if (!$add)
      return $this->redirectWithError("Une erreur s'est produite lors de l'ajout de l'article au panier.");

    Application::$instance->session->updateBasket();
    return $this->redirectWithSuccess("L'article a bien été ajouté au panier.");
  }

  public function delete(Request $request)
  {
    $id = $request->getParamId();
    if (!$id)
      return $this->redirectNotFound();

    if (!$this->hasSessionUser())
      return $this->redirectToLogin();

    if (!Application::$instance->session->isInBasket($id))
      return $this->redirectWithError("Cet article n'est pas dans votre panier.");

    $deletion = Application::$instance
      ->database
      ->deleteFromBasket($id);

    if (!$deletion)
      return $this->redirectWithError("Une erreur s'est produite lors de la suppression de l'article.");

    Application::$instance->session->updateBasket();
    return $this->redirectWithSuccess("L'article a bien été supprimé du panier.");
  }
}
