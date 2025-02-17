<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Load Composer's autoloader
require '../vendor/autoload.php';
require_once '../Database/db.php';

// Start the session
session_start();

// Load environment variables
$dotenv = Dotenv::createImmutable('../');
$dotenv->load();

if (isset($_POST['reg_new'])) {
    $new_user = $_POST['new_email']; 
    $token = bin2hex(random_bytes(16)); // Generate a unique token
    $expires_at = date("Y-m-d H:i:s", strtotime('+1 hour')); // Set expiration time to 1 hour from now

    // Check if the user already exists
    $sql = "SELECT * FROM users WHERE email = :email"; 
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $new_user, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch a single row

    if ($result) {
        echo '<div class="alert alert-warning container mt-5 w-50" role="alert">
              <i class="bi bi-envelope-exclamation me-3"></i> The email address already exists. Please use a different email address.
              </div>';
    } else {
        // Check if the user already exists in the user_tokens table
        $sql = "SELECT * FROM user_tokens WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $new_user, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC); // Use fetch() instead of get_result()

        if ($result) {
            // Update the token and expiration time
            $update_token = "UPDATE user_tokens SET token = :token, expires_at = :expires_at WHERE email = :email";
            $stmt = $conn->prepare($update_token);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->bindParam(':expires_at', $expires_at, PDO::PARAM_STR);
            $stmt->bindParam(':email', $new_user, PDO::PARAM_STR);
        } else {
            // Insert the token into the user_tokens table
            $insert_token = "INSERT INTO user_tokens (email, token, expires_at) VALUES (:email, :token, :expires_at)";
            $stmt = $conn->prepare($insert_token);
            $stmt->bindParam(':email', $new_user, PDO::PARAM_STR);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->bindParam(':expires_at', $expires_at, PDO::PARAM_STR);
        }

        if ($stmt->execute()) {
            // Send the email with the registration link
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $_ENV['GMAIL_USERNAME']; // Your Gmail address
                $mail->Password = $_ENV['GMAIL_APP_PASSWORD']; // Your Gmail App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom($_ENV['GMAIL_USERNAME'], 'Campus Planner');
                $mail->addAddress($new_user, 'Coder Info');
                $mail->addReplyTo($_ENV['GMAIL_USERNAME'], 'Campus Planner');

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Register to Campus Planner';
                $mail->Body = '
                <!DOCTYPE html>
                <html lang="en">
                <head>
                  <meta charset="UTF-8">
                  <meta name="viewport" content="width=device-width, initial-scale=1.0">
                  <title>Campus Planner</title>
                  <style>
                      body {
                          margin: 0;
                          padding: 0;
                          box-sizing: border-box;
                          font-family: Arial, sans-serif;
                          font-size: 20px;
                          line-height: 1.5;
                          color: #333;
                          background-color: #f8f9fa;
                      }
                      .container {
                          max-width: 600px;
                          margin: 0 auto;
                          padding: 20px;
                          background-color: #ffffff;
                          border-radius: 10px;
                          box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                      }
                      .title {
                          text-align: center;
                          margin-bottom: 20px;
                          font-size: 24px;
                      }
                      .content {
                          margin-bottom: 20px;
                          text-align: center;
                      }
                      .register-link {
                          text-align: center;
                          margin: 30px 0;
                      }
                      .register-link a {
                          background-color: #71c55d;
                          color: #ffffff;
                          padding: 10px 20px;
                          text-decoration: none;
                          border-radius: 5px;
                      }
                      .register-link a:hover {
                          background-color: #5a9c4a;
                      }
                      footer {
                          text-align: center;
                          color: #777777;
                          font-size: 15px;
                      }
                  </style>
              </head>
              <body>
                  <div class="container">
                      <div class="title">
                          <h1>Campus planner</h1>
                      </div>
                      <div class="content">
                          <p>Thank you for your interest in Campus planner. <br> Kindly use the following link to register!</p>
                      </div>
                      <div class="register-link">
                          <a href="http://localhost/projects/event-management/Auth/email_callback.php?token='.urlencode($token).'">Register Now</a>
                          <p>The link expires in 1 hour</p>
                      </div>
                      <footer>
                          <p>Best Regards,</p>
                          <p><strong>Everline Senoi</strong></p>
                      </footer>
                  </div>
              </body>
              </html>';

                $mail->AltBody = 'Hello! Welcome to Campus Planner.';

                $mail->send();
                echo '<div class="container w-50  alert alert-success mt-5" role="alert">
                      <i class="bi bi-check-circle-fill"></i> Registration link sent successfully! Check your email.
                      </div>';
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "<script>alert('Failed to generate token');</script>";
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
         
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        
</head>
<body style="display-flex:flex; align-items:center; justify-content:center; height:100vh;">

    <!-- Register Modal -->
    <div class="modal d-flex align-items-center justify-content-center flex-column">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <p class="modal-title fs-5" id="registerModalLabel">Enter your Email address below to receive the Registration link!</p>
                </div>
                <div class="modal-body">
                    <form id="register-form" method="post" action="register.php">
                        <div class="form-group">
                            <label for="register_email">Email address<span class="text-danger">*</span></label>
                            <input type="email" name="new_email" class="form-control py-2" id="register_email" placeholder="Enter your email" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" name="back" onclick="window.location='index.php'" >Back</button>
                            <button type="submit" class="btn btn-success" id="sendemail" name="reg_new">Send Link</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>