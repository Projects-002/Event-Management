<?php
// connect to the database
include "../Database/db.php";

// Email and pass authentication

if (isset($_POST['login-email'])) {

    $email = $_POST['email'];
    $pass = $_POST['password'];

    // Check if the user exists
    $sql = "SELECT * FROM users WHERE email = :email AND User_Role = 'admin'";
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
            header('location: ../admin/index.php');
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
    <title>Document</title>
</head>
<!-- Boostrap css -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<!-- Font awesome css -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
<!-- Custom css -->
<link rel="stylesheet" href="style.css">

<style>
    body{
        margin: 0;
        padding: 0;
        background-color: #f1f1f1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100vh;

    }
    .google-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 20px;
    background-color: #ffffff;
    border: 1px solid #dfdfdf;
    border-radius: 4px;
    color: #333333;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.google-btn img {
    width: 30px;
    margin-right: 10px;
}

.google-btn:hover {
    background-color:rgb(255, 255, 255);
}
</style>
<body style="text-align: center;">

    <form class="form-signin" action="index.php" method="POST">

       <div class="top d-flex justify-content-center flex-column align-items-center mb-4">
        <img class="mb-4" src="../assets/image/logo.png" alt="" width="72" height="72">
        <h1 class="h3 mb-3 font-weight-normal">Campus planner</h1>
       </div> 

      <label for="inputEmail" class="sr-only">Email address</label>
      <input type="email" name="email" id="inputEmail" class="form-control mb-4" placeholder="Email address" required="" autofocus="">
      <label for="inputPassword" class="sr-only">Password</label>
      <input type="password" id="inputPassword" class="form-control" name="password" placeholder="Password" required="">
      <div class="checkbox mb-3">
        <label class="d-flex justify-content-left align-items-center my-3">
         <span>Forgot Password? <a href="">Reset</a></span> 
        </label>
      </div>
      <a class="btn btn-lg btn-success btn-block mb-4" href="home.php">Sign in</a>
       
      <!-- OR separator -->
        <div class="d-flex align-items-center mb-4">
            <div class="flex-grow-1 border-top"></div>
            <span class="mx-2 text-muted">OR</span>
            <div class="flex-grow-1 border-top"></div>
        </div>

      <button class="google-btn">
        <img src="https://img.icons8.com/color/48/000000/google-logo.png" alt="Google Logo">
        <span>Continue with Google</span>
      </button>
      <p class="mt-5 mb-3 text-muted">© 2024-2025</p>
    </form>


</body>
</html>


