<?php

namespace app\controllers;

use app\core\Request;
use app\core\Controller;
use app\core\Application;

class BasketController extends Controller
{
  private function isInBasket(int $user_id, int $product_id): bool
  {
    return (bool) Application::$instance
      ->database
      ->findOne("basket", ["*"], [
        "product_id" => $product_id,
        "buyer_id" => $user_id
      ]);
  }

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
      "success_message" => $success_message,
      "user" => $this->getSessionUser()
    ]);
  }

  public function my_basket()
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    $user["basket"] = Application::$instance
      ->database
      ->getBasket((int)$user["id"]);

    return $this->render("user/my-basket", [
      "user" => $user,
    ]);
  }

  public function add(Request $request)
  {
    $id = $request->getParamId();
    if (!$id)
      return $this->redirectNotFound();

    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    $product = Application::$instance
      ->database
      ->findProductById($id);

    if (!$product)
      return $this->redirectWithError("L'article n'existe pas.");

    if ($this->isInBasket((int)$user["id"], $id))
      return $this->redirectWithError("Cet article est déjà dans votre panier.");

    $user = $this->getSessionUser();
    $add = Application::$instance
      ->database
      ->addToBasket($product, (int)$user["id"]);

    if (!$add)
      return $this->redirectWithError("Une erreur s'est produite lors de l'ajout de l'article au panier.");

    return $this->redirectWithSuccess("L'article a bien été ajouté au panier.");
  }

  public function delete(Request $request)
  {

    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    $basket_id = $request->getParamId();
    if (!$basket_id)
      return $this->redirectNotFound();

    if (!($basketProduct = Application::$instance->database->findOne("basket", ["*"], ["id" => $basket_id])))
      return $this->redirectNotFound();

    $user_id = (int) $user["id"];

    if ((int)$basketProduct["buyer_id"] !== $user_id)
      return $this->redirectWithError("Cet article n'est pas dans votre panier.");

    $deletion = Application::$instance
      ->database
      ->deleteFromBasket($basket_id);

    if (!$deletion)
      return $this->redirectWithError("Une erreur s'est produite lors de la suppression de l'article.");

    return $this->redirectWithSuccess("L'article a bien été supprimé du panier.");
  }
}
