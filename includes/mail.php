<?php 
require_once  'config.php';

class Mail {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer\PHPMailer\PHPMailer(true);
        $this->configureMailer();
    }
    
    private function configureMailer() {
        try {
            $this->mailer->SMTPDebug = $_ENV['MAIL_DEBUG'] ?? 0;
            $this->mailer->isSMTP();
            $this->mailer->Host = $_ENV['MAIL_HOST'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $_ENV['MAIL_USERNAME'];
            $this->mailer->Password = $_ENV['MAIL_PASSWORD'];
            $this->mailer->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
            $this->mailer->Port = $_ENV['MAIL_PORT'] ?? 587;
        
            $this->mailer->CharSet = 'UTF-8';
            
        } catch (Exception $e) {
            error_log("Mailer configuration error: " . $e->getMessage());
        }
    }

    public function send($data) {
        try {
            $this->mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
            $this->mailer->addAddress($data['to']);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $data['subject'];
            
            $templatePath = __DIR__ . '/../templates/emails/' . $data['template'] . '.php';
            if (file_exists($templatePath)) {
                ob_start();
                extract($data['data']);
                include $templatePath;
                $this->mailer->Body = ob_get_clean();
            } else {
                $this->mailer->Body = $data['message'] ?? 'No message content provided';
            }
            
            $this->mailer->AltBody = strip_tags($this->mailer->Body);
            
            $this->mailer->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Mail send error: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
}