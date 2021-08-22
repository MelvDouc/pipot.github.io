<?php

use app\core\Application;
use app\controllers\AuthController;
use app\controllers\SiteController;
use app\controllers\ProductController;
use app\controllers\ProfileController;
use app\controllers\RegisterController;

require "../vendor/autoload.php";

$app = new Application(dirname(__DIR__));

$app->get("", [SiteController::class, "home"]);
$app->get("accueil", [SiteController::class, "home"]);
$app->get("categorie", [SiteController::class, "category"]);
$app->get("categories", [SiteController::class, "categories"]);
$app->get("article", [ProductController::class, "product"]);
$app->get("ajouter-article", [ProductController::class, "add"]);
$app->post("ajouter-article", [ProductController::class, "add"]);
$app->get("connexion", [AuthController::class, "login"]);
$app->post("connexion", [AuthController::class, "login"]);
$app->get("inscription", [RegisterController::class, "register"]);
$app->post("inscription", [RegisterController::class, "register"]);
$app->get("validation", [RegisterController::class, "validation"]);
$app->post("deconnexion", [AuthController::class, "logout"]);
$app->get("mon-profil", [ProfileController::class, "my_profile"]);
$app->get("profil", [ProfileController::class, "profile"]);
$app->get("mes-articles", [ProfileController::class, "my_products"]);

$app->run();
