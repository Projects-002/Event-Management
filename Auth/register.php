<?php
use PHPMailer\PHPMailer\PHPMailer;// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\Exception;// Import PHPMailer classes into the global namespace
Use Dotenv\Dotenv;// Import Dotenv classes into the global namespace

// Load Composer's autoloader
include '../vendor/autoload.php';
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
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $new_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<div class="alert alert-warning container mt-5 w-50" role="alert">
              <i class="bi bi-envelope-exclamation me-3"></i> The email address already exists. Please use a different email address.
              </div>';
    } else {

        // Check if the user already exists in the user_tokens table
        $sql = "SELECT * FROM user_tokens WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $new_user);
        $stmt->execute();
        $result = $stmt->get_result();


        if ($result->num_rows > 0) {
            // Update the token and expiration time
            $update_token = "UPDATE user_tokens SET token = ?, expires_at = ? WHERE email = ?";
            $stmt = $conn->prepare($update_token);
            $stmt->bind_param("sss", $token, $expires_at, $new_user);
            $stmt->execute();
        } else {

        // Insert the token into the user_tokens table
        $insert_token = "INSERT INTO user_tokens (email, token, expires_at) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_token);
        $stmt->bind_param("sss", $new_user, $token, $expires_at);

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
                $mail->setFrom($_ENV['GMAIL_USERNAME'], 'Portfolio Ready');
                $mail->addAddress($new_user, 'Coder Info');
                $mail->addReplyTo($_ENV['GMAIL_USERNAME'], 'Portfolio Ready');

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Register to Portfolio Ready';
                $mail->Body = '
                <!DOCTYPE html>
                <html lang="en">
                <head>
                  <meta charset="UTF-8">
                  <meta name="viewport" content="width=device-width, initial-scale=1.0">
                  <title>Portfolio Ready</title>
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
                      .title h1 {
                          color: #333333;
                      }
                      .content {
                          margin-bottom: 20px;
                      }
                      .content p {
                          color: #555555;
                          line-height: 1.6;
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
                      footer p {
                          margin: 5px 0;
                      }
                      footer a {
                          color: #007bff;
                          text-decoration: none;
                      }
                      footer a:hover {
                          text-decoration: underline;
                      }
                  </style>
              </head>
              <body>
                  <div class="container">
                      <div class="title">
                          <h1>Portfolio Ready</h1>
                      </div>
                      <div class="content">
                          <p>Thank you for your interest in portfolio ready. <br> Your software development success begins here!. <br> Kindly use the following link to register!</p>
                      </div>
                      <div class="register-link">
                          <a href="http://localhost/PortfolioReady/Auth/email_callback.php?token='.urlencode($token).'">Register Now</a>
                          <p>The link expires in 1 hour</p>
                      </div>
                      <footer>
                          <p>Best Regards,</p>
                          <p><strong>Astra Softwares</strong></p>
                          <p><a href="https://astrasoft.tech">www.astrasoft.tech</a></p>
                          <p>info.astrasoft.tech</p>
                          <p>All rights reserved.</p>
                      </footer>
                  </div>
              </body>
              </html>';

                $mail->AltBody = 'Hello! Welcome to Portfolio Ready.';

                $mail->send();
                echo '<div class="container w-50  alert alert-success mt-5" role="alert">
                      <i class="bi bi-check-circle-fill"></i> Registration link sent successfullly Kindly check your email;
                      </div>';
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "<script>alert('Failed to generate token');</script>";
        }
    }
    
    // Close the statement
    $stmt->close();
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