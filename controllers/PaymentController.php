<?php

namespace app\controllers;

use Dotenv\Dotenv;
use app\core\Request;
use app\core\Controller;
use Stripe\StripeClient;
use app\core\Application;

$dotenv = Dotenv::createImmutable(Application::$ROOT_DIR);
$dotenv->load();

class PaymentController extends Controller
{
  public function buyPipots(Request $request)
  {
    if (!($user = $this->getSessionUser()))
      return $this->redirectToLogin();

    $full_name = $user["first_name"] . " " . $user["last_name"];

    if ($request->isGet())
      return $this->render("payments/index", [
        "full_name" => $full_name
      ]);

    $email = $user["email"];
    $stripe = new StripeClient($_ENV["STRIPE_SECRET_KEY"]);
    $customer = $stripe->customers->create([
      "email" => $email,
      "name" => $_POST["full_name"],
      "payment_method" => "pm_card_visa",
      "invoice_settings" => [
        "default_payment_method" => "pm_card_visa"
      ],
      "preferred_locales" => ["fr"]
    ]);
    $intent = $stripe->paymentIntents->create([
      "amount" => floor((int)$_POST["quantity"]),
      "currency" => "eur"
    ]);
    $intent = $stripe->paymentIntents->confirm([
      "id" => $intent->id,
      "payment_method" => "pm_card_visa"
    ]);
  }
}
