<?php

use app\admin\AdminController;
use app\core\Application;
use app\controllers\AuthController;
use app\controllers\SiteController;
use app\controllers\BasketController;
use app\controllers\ProductController;
use app\controllers\ProfileController;
use app\controllers\CategoryController;
use app\controllers\RegisterController;
use app\admin\PanelController as AdminPanelController;
use app\admin\ProductController as AdminProductController;
use app\admin\UserController as AdminUserController;
use app\controllers\MessageController;
use app\controllers\PaymentController;
use app\controllers\RatingController;

require "../vendor/autoload.php";

$app = new Application(dirname(__DIR__));

$app->get("", [SiteController::class, "home"]);
$app->get("accueil", [SiteController::class, "home"]);

// Catégories
$app->get("categorie", [CategoryController::class, "category"]);
$app->get("categories", [CategoryController::class, "categories"]);

// Article
$app->get("article", [ProductController::class, "product"]);
$app->get("ajouter-article", [ProductController::class, "add"]);
$app->post("ajouter-article", [ProductController::class, "add"]);
$app->get("modifier-article", [ProductController::class, "update"]);
$app->post("modifier-article", [ProductController::class, "update"]);
$app->post("supprimer-article", [ProductController::class, "delete"]);

// Panier
$app->get("mon-panier", [BasketController::class, "my_basket"]);
$app->post("ajouter-au-panier", [BasketController::class, "add"]);
$app->post("supprimer-du-panier", [BasketController::class, "delete"]);

// Inscription
$app->get("inscription", [RegisterController::class, "register"]);
$app->post("inscription", [RegisterController::class, "register"]);
$app->get("validation", [RegisterController::class, "validation"]);

// Connexion
$app->get("connexion", [AuthController::class, "login"]);
$app->post("connexion", [AuthController::class, "login"]);
$app->post("deconnexion", [AuthController::class, "logout"]);

// Profil
$app->get("mon-profil", [ProfileController::class, "my_profile"]);
$app->get("profil", [ProfileController::class, "profile"]);
$app->get("modifier-mon-mot-de-passe", [ProfileController::class, "updatePassword"]);
$app->post("modifier-mon-mot-de-passe", [ProfileController::class, "updatePassword"]);
$app->get("modifier-mes-coordonnees", [ProfileController::class, "updateContact"]);
$app->post("modifier-mes-coordonnees", [ProfileController::class, "updateContact"]);
$app->get("mes-articles", [ProfileController::class, "my_products"]);

// Messages
$app->get("messagerie", [MessageController::class, "my_messages"]);
$app->post("messagerie", [MessageController::class, "my_messages"]);

// Notation
$app->post("noter", [RatingController::class, "rate"]);

// Recherche de produits
$app->post("recherche", [ProductController::class, "search"]);

// Achat de Pipots
$app->get("acheter-des-pipots", [PaymentController::class, "buyPipots"]);
$app->post("acheter-des-pipots", [PaymentController::class, "buyPipots"]);

// Admin
$app->get("admin", [AdminPanelController::class, "panel"]);
$app->post("admin-connexion", [AdminController::class, "login"]);
$app->get("admin-articles", [AdminPanelController::class, "all_products"]);
$app->get("admin-utilisateurs", [AdminPanelController::class, "all_users"]);
$app->get("admin-modifier-article", [AdminProductController::class, "update"]);
$app->post("admin-modifier-article", [AdminProductController::class, "update"]);
$app->get("admin-modifier-utilisateur", [AdminUserController::class, "update"]);
$app->post("admin-modifier-utilisateur", [AdminUserController::class, "update"]);
$app->post("admin-supprimer-article", [AdminProductController::class, "delete"]);

$app->run();
