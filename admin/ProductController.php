<?php

namespace app\admin;

use app\core\Request;
use app\models\Product;

class ProductController extends AdminController
{
  private function update_get(Product $product)
  {
    return $this->render("products/update", [
      "title" => "Modifier un article",
      "product" => $product,
      "flashSuccess" => $this->getFlash("success"),
      "flashErrors" => $this->getFlash("errors"),
    ]);
  }

  public function update(Request $request)
  {
    if (!$this->isLoggedAsAdmin())
      return $this->redirectHome();

    if (!($id = $request->getParamId()))
      return $this->redirectNotFound();

    if (!($product = Product::findOne(["id" => $id])))
      return $this->redirectNotFound();

    if ($request->isGet())
      return $this->update_get($product);

    if (!$product->isValid()) {
      $this->setFlash("errors", $product->getErrors());
      return $this->update_get($product);
    }

    $product->update();
    $this->setFlash("success", "L'article a bien été modifié.");
    return $this->redirect("/admin/articles");
  }

  public function delete(Request $request)
  {
    if (!$this->isLoggedAsAdmin())
      return $this->redirectHome();

    if (!($id = $request->getParamId()))
      return $this->redirectNotFound();

    if (!($product = Product::findOne(["id" => $id])))
      return $this->redirectNotFound();

    $this->setFlash("success", "L'article a bien été supprimé.");
    return $this->redirect("/admin/articles");
  }
}
