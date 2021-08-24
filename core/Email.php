<?php

namespace app\core;

use Dotenv\Dotenv;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;

$dotenv = Dotenv::createImmutable(Application::$ROOT_DIR);
$dotenv->load();

class Email
{
  private const SMTPDebug = 0;
  private const HOST = "smtp.gmail.com";
  private PHPMailer $email;
  private string $recipient_address;
  private string $recipient_username;
  private string $subject;

  public function __construct(string $recipient_address, string $recipient_username, string $subject)
  {
    $this->recipient_address = $recipient_address;
    $this->recipient_username = $recipient_username;
    $this->subject = $subject;
    $mail = new PHPMailer(true);
    $mail->SMTPDebug = self::SMTPDebug;
    $mail->isSMTP();
    $mail->Host = self::HOST;
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV["GMAIL_ADDRESS"];
    $mail->Password = $_ENV["GMAIL_APP_PASSWORD"];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;
    $mail->CharSet = "UTF-8";
    $mail->setFrom($mail->Username, "Admin PIPOT");
    $mail->addAddress($this->recipient_address, $this->recipient_username);
    $mail->isHTML(true);
    $mail->Subject = $this->subject;
    $this->email = $mail;
  }

  public function set_confirmation_HTML_body($verification_string)
  {
    $domain_name = $_ENV["DOMAIN_NAME"];
    $this->email->Body = "
    <h1>Bonjour, $this->recipient_username,</h1>

    <p>Votre inscription a bien été enregistrée. Il ne vous reste plus qu'à cliquer le lien suivant pour activer votre compte.</p>
    <p style='margin-bottom: 2rem;'><a href='$domain_name/validation/$verification_string'>Activer le compte</a></p>

    <div style='text-align: right;'>Bon troc !<br>L'équipe PIPOT</div>
    ";
  }

  public function set_confirmation_alt_body($verification_string)
  {
    $domain_name = $_ENV["DOMAIN_NAME"];
    $this->email->AltBody = "
    Bonjour, $this->recipient_username,\n\n
    Votre inscription a bien été enregistrée. Il ne vous reste plus qu'à cliquer le lien suivant pour activer votre compte.\n
    $domain_name/validation/$verification_string'\n\n

    Bon troc !\nL'équipe PIPOT";
  }

  public function send()
  {
    try {
      $this->email->send();
    } catch (Exception $e) {
      echo "Message could not be sent. Mailer Error: {$this->email->ErrorInfo}";
    }
  }
}
