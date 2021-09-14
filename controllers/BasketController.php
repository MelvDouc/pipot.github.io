<?php

namespace app\controllers;

use app\core\Request;
use app\core\Controller;
use app\models\Product;

class BasketController extends Controller
{
  public function myBasket()
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    return $this->render("user/my-basket", [
      "title" => "Mon panier",
      "user" => $user,
      "flashSuccess" => $this->getFlash("success"),
      "flashErrors" => $this->getFlash("errors")
    ]);
  }

  public function add(Request $request)
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    if (!($id = $request->getParamId()))
      return $this->redirectNotFound();

    $product = Product::findOne(["id" => $id]);

    if (!$product || $product->seller_id === $user->id || $user->hasInBasket($product)) {
      $this->setFlash("errors", ["Impossible d'ajouter cet article à votre panier."]);
      return $this->redirect("/mon-panier");
    }

    $user->addToBasket($product);
    $this->setFlash("success", "L'article a été ajouté au panier.");
    return $this->redirect("/mon-panier");
  }

  public function delete(Request $request)
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    if (!($id = $request->getParamId()))
      return $this->redirectNotFound();

    $product = Product::findOne(["id" => $id]);

    if (!$product || !$user->hasInBasket($product)) {
      $this->setFlash("errors", ["Impossible de retirer cet article de votre panier."]);
      return $this->redirect("/mon-panier");
    }

    $user->removeFromBasket($product);
    $this->setFlash("success", "L'article a été retiré panier.");
    return $this->redirect("/mon-panier");
  }
}
