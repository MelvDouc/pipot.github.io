CREATE DATABASE IF NOT EXISTS pipot;

USE pipot;

CREATE TABLE IF NOT EXISTS users (
  id INT NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL,
  email VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(10) NOT NULL DEFAULT "USER",
  first_name VARCHAR(50) NULL,
  last_name VARCHAR(50) NULL,
  postal_address VARCHAR(255) NULL,
  city VARCHAR(50) NULL,
  zip_code VARCHAR(10) NULL,
  phone_number VARCHAR(20) NULL,
  verification_string VARCHAR(128) NULL,
  is_account_active TINYINT(4) NOT NULL DEFAULT 0,
  profile_pic VARCHAR(100) NULL DEFAULT "_default.jpg",
  added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id)
);

CREATE TABLE IF NOT EXISTS categories (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  description TEXT NOT NULL,
  image VARCHAR(100) NOT NULL DEFAULT "_default.jpg",
  added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id)
);

CREATE TABLE IF NOT EXISTS products (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  description TEXT NOT NULL,
  price INT(11) NOT NULL,
  quantity INT(11) NOT NULL,
  seller_id INT(11) NOT NULL,
  category_id INT(11) NOT NULL,
  image VARCHAR(100) NOT NULL DEFAULT "_default.jpg",
  added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  FOREIGN KEY (seller_id) REFERENCES users(id),
  FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE IF NOT EXISTS basket (
  id INT NOT NULL AUTO_INCREMENT,
  product_id INT(11) NOT NULL,
  buyer_id INT(11) NOT NULL,
  added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (buyer_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS direct_messages (
  id INT NOT NULL AUTO_INCREMENT,
  sender_id INT(11) NOT NULL,
  recipient_id INT(11) NOT NULL,
  subject VARCHAR(50) NOT NULL,
  content TEXT NOT NULL,
  added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  FOREIGN KEY (sender_id) REFERENCES users(id),
  FOREIGN KEY (recipient_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS ratings (
  id INT NOT NULL AUTO_INCREMENT,
  score INT(2) NOT NULL,
  rater_id INT(11) NOT NULL,
  rated_id INT(11) NOT NULL,
  added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  FOREIGN KEY (rater_id) REFERENCES users(id),
  FOREIGN KEY (rated_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS events (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  description TEXT NOT NULL,
  author_id INT NOT NULL,
  start_date DATETIME NOT NULL,
  end_date DATETIME NOT NULL,
  image VARCHAR(100) NOT NULL DEFAULT "_default.jpg",
  added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id),
  FOREIGN KEY (author_id) REFERENCES users(id)
);

-- INSERT INTO users (username, email, password, role, is_account_active)
-- VALUES
-- ("admin", "webmaster.pipot.wf3@gmail.com", "$2b$10$Jfj4//4bcobPrUWzoojPS.KHwVnnr8dhROJjJ2eEfFDwhfnHVU0.y", "ADMIN", 1),
-- ("firstUser", "john@doe.com", "$2b$10$Jfj4//4bcobPrUWzoojPS.KHwVnnr8dhROJjJ2eEfFDwhfnHVU0.y", "USER", 1);

-- INSERT INTO categories (name, description, image)
-- VALUES
-- ("Vêtements", "Articles d'habillement.", "vetements.jpg"),
-- ("Accessoires", "Objets et compléments de vêture en tous genres.", "accessoires.jpg");

-- INSERT INTO products (name, description, price, quantity, seller_id, category_id, image)
-- VALUES
-- ("Gants", "Des gants élastiques", 5, 1, 1, 2, "c78e23bc-1cd2-4827-b9ea-e858f31c496d.png"),
-- ("Pullover", "Un pullover des années 80", 10, 1, 1, 1, "14ffa3aff55cf277188fe8f41e105872.png"),
-- ("Jean", "Un jean troué", 12, 1, 1, 1, "_default.jpg"),
-- ("Écharpe", "Une écharpe de couleur beige", 7, 1, 1, 1, "_default.jpg");