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

// Signin With Google Account Start
$client = new Google\Client;

$client ->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);

$client->addScope("email");
$client->addScope("profile");

$url = $client->createAuthUrl();
// Google Auth End



//Email and pass authentication

if (isset($_POST['login-email'])) {

    $email = $_POST['email'];
    $pass = $_POST['pass'];

    // Check if the user exists
    $sql = "SELECT * FROM users WHERE email = :email AND User_Role = 'user'";
    $stmt = $conn->prepare($sql);
    
    // Use bindValue or bindParam to bind the email parameter
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    // Check if any user was found
    if ($stmt->rowCount() > 0) {
        // Get the user information
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the password
        if (password_verify($pass, $user['Pass'])) {
            $_SESSION['email'] = $user['Email'];
            $_SESSION['name'] = $user['First_Name'] . ' ' . $user['Last_Name'];
            $_SESSION['id'] = $user['SN'];
            $_SESSION['avatar'] = $user['Avatar'];

            // Get the user ID
            $id = $user['SN']; // This assumes the SN column is the user ID

            // Store user information in session
            $_SESSION['email_auth'] = $id;

            // Redirect to home page
            header('location: ../eventcalendar/index.php');
            exit();
        } else {
            echo "<script>alert('Incorrect email or password');</script>";
        }
    } else {
        echo "<script>alert('Incorrect email or password');</script>";
    }
}
?>

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Campus Planner | Authethication-Page</title>
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <!-- Custom CSS -->
        <link rel="stylesheet" href="styles.css">
    </head>
 <body>

    <!-- internal css -->
    <style>
        body {
            background-color: #f9f9f9;
        }

        .logo{
        background-image:url("../assets/img/logobg1.png");
        width: 150px;
        height: 150px;
        background-position:center;
        background-size:contain;
        background-repeat:no-repeat;
        }

        .card {
            border: none;
            border-radius: 10px;
        }

        .btn-outline-secondary {
            color: #555;
            border-color: #ddd;
        }

        .btn-outline-secondary:hover {
            background-color:rgb(184, 184, 184);
            border-color: #ccc;
        }

        .form-control {
            border-radius: 8px;
        }
    </style>

    <!-- Main Content -->
    <div class="container d-flex justify-content-center align-items-center flex-column" style="min-height: 100vh;">
        <div class="card shadow-lg p-4" style="width: 100%; max-width: 400px;">
        
            <!-- Logo -->
             <p class="text-center">Campus Planner</p>
             <!-- <div class="logo  d-flex justify-content-center align-items-center container p-0" style="background-image:url('../assets/images/gree.png');"></div> -->
            <h2 class="text-center font-weight-bold mb-4">Sign In</h2>
            
            <!-- Email Address Input -->
            <form id="signup-form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?> ">
                
                <div class="form-group">
                    <label for="email">Email address<span class="text-danger">*</span></label>
                    <input type="email"  name="email" class="form-control py-2" id="email" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password<span class="text-danger">*</span></label>
                    <input type="password"  name="pass" class="form-control py-2" id="password" placeholder="Enter your password" required>
                </div>
             
                <div class="my-2">
                <small>Forgot Password? <a href="#" class="text-success" data-bs-toggle="modal" data-bs-target="#resetModal">Reset</a></small>
                </div>

                <button type="submit" name="login-email" class="btn btn-success btn-block">Continue</button>
            </form>

            <!-- Already have an account -->
            <div class="text-center my-3">
                <small>Don't have account yet? <a href="register.php" class="text-success">Register</a></small><br>
            </div>

            <!-- OR separator -->
            <div class="d-flex align-items-center">
                <div class="flex-grow-1 border-top"></div>
                <span class="mx-2 text-muted">OR</span>
                <div class="flex-grow-1 border-top"></div>
            </div>

            <!-- Social Login Buttons -->
            <div class="mt-3">
                 <!-- Google auth link -->
                 <a href='<?= $url ?>' class="btn btn-outline-secondary btn-block mb-2 d-flex align-items-center justify-content-center">
                    <img src="google.png" width="20" class="mr-2">
                    Continue with Google Account
                 </a>
            </div>
        </div>
    </div>


    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetModal" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <p class="modal-title fs-5" id="resetModalLabel">Enter your Email address below to receive the reset link!</p>
                </div>
                <div class="modal-body">
                    <form id="reset-form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                        <div class="form-group">
                            <label for="reset_email">Email address<span class="text-danger">*</span></label>
                            <input type="email" name="reset_email" class="form-control py-2" id="reset_email" placeholder="Enter your email" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success" name="reset_password">Send Link</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>


    <!-- reset password modal -->
    <div class="modal fade" id="resetModal" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
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

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Custom JS -->
    
    <script>
        const myModal = document.getElementById('myModal')
        const myInput = document.getElementById('myInput')

        myModal.addEventListener('shown.bs.modal', () => {
        myInput.focus()
        })
    </script>

</body>
</html>


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

                $mail->setFrom($_ENV['GMAIL_USERNAME'], 'Portfolio Ready');
                $mail->addAddress($reset_email, 'User');
                $mail->addReplyTo($_ENV['GMAIL_USERNAME'], 'Portfolio Ready');

                $mail->isHTML(true);
                $mail->Subject = 'Reset Your Password';
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
                          <h1>Portfolio Ready</h1>
                      </div>
                      <div class="content">
                          <p>We received a request to reset your password. <br> Click the link below to reset your password!</p>
                      </div>
                      <div class="reset-link">
                          <a href="http://localhost/PortfolioReady/Auth/reset_password.php?token='.urlencode($token).'">Reset Password</a>
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
