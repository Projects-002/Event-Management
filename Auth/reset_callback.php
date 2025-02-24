<!-- reset password section -->
<?php


if (isset($_POST['reset_password'])) {

    // Escape user input
    $reset_email = mysqli_real_escape_string($conn, $_POST['reset_email']);
    $token = bin2hex(random_bytes(16));
    $expires_at = date("Y-m-d H:i:s", strtotime('+1 hour'));

    // Check if the email exists
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $reset_email);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the email exists
    if ($result->num_rows > 0) {
        $sql = "SELECT * FROM user_tokens WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $reset_email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the user already exists in the user_tokens table
        if ($result->num_rows > 0) {
            $update_token = "UPDATE user_tokens SET token = ?, expires_at = ? WHERE email = ?";
            $stmt = $conn->prepare($update_token);
            $stmt->bind_param("sss", $token, $expires_at, $reset_email);
            $stmt->execute();
        } else {
            $insert_token = "INSERT INTO user_tokens (email, token, expires_at) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_token);
            $stmt->bind_param("sss", $reset_email, $token, $expires_at);
            $stmt->execute();
        }

        // Send the email with the reset link
        if ($stmt->execute()) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $_ENV['GMAIL_USERNAME'];
                $mail->Password = $_ENV['GMAIL_APP_PASSWORD'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom($_ENV['GMAIL_USERNAME'], 'Campus Planner');
                $mail->addAddress($reset_email, 'User');
                $mail->addReplyTo($_ENV['GMAIL_USERNAME'], 'Campus Planner');

                $mail->isHTML(true);
                $mail->Subject = 'Reset Your Password';
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
                      .reset-link {
                          text-align: center;
                          margin: 30px 0;
                      }
                      .reset-link a {
                          background-color: #71c55d;
                          color: #ffffff;
                          padding: 10px 20px;
                          text-decoration: none;
                          border-radius: 5px;
                      }
                      .reset-link a:hover {
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
                          <h1>Campus Planner</h1>
                      </div>
                      <div class="content">
                          <p>We received a request to reset your password. <br> Click the link below to reset your password!</p>
                      </div>
                      <div class="reset-link">
                          <a href="http://localhost/Projects/event-management/Auth/reset_password.php?token='.urlencode($token).'">Reset Password</a>
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
              </html>  
                ';

                $mail->AltBody = 'We received a request to reset your password. Click the link to reset your password.';

                $mail->send();
                echo "<script>alert('Reset link sent successfully!');</script>";
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "<script>alert('Failed to generate token');</script>";
        }
    } else {
        echo "<script>alert('Email not found');</script>";
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

    <!-- boostrap css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- custom css -->

</head>
<body>

  <!-- reset password modal -->
  <div class="modal fade" >
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <p class="modal-title fs-5" id="exampleModalLabel"> Enter your Email address below to receive the Registration link! </p>
            <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">close</button> -->
        </div>
        <div class="modal-body">
            <form id="signup-form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="form-group">
                        <label for="new_email">Email address<span class="text-danger">*</span></label>
                        <input type="email"  name="new_email" class="form-control py-2" id="email" placeholder="Enter your email" required>
            </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success" name="reg_new">Send Link</button>
        </div>
        </form>
        </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>