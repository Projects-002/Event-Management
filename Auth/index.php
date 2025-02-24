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
            header('location: ../eventmanager/index.php');
            exit();
        } else {
            echo "<script>alert('Incorrect email or password');</script>";
        }
    } else {
        echo "<script>alert('Incorrect email or password');</script>";
    }
}
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
                <small>Forgot Password? <a href="./reset_callback.php" class="text-success" data-bs-toggle="modal" data-bs-target="#resetModal">Reset</a></small>
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



