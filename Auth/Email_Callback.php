<?php

//check url for token
if(!isset($_GET['token'])){
    echo "Token not found.";
    exit();

}else{
        // Include the database configuration file
        require_once '../database/db.php';
        // Get the token from the URL
        $token = $_GET['token'];
        //check if token is in database
        $sql = "SELECT * FROM user_tokens WHERE token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $token_data = $result->fetch_assoc();
    
        // Check if the token is valid and not expired
        if ($token_data && strtotime($token_data['expires_at']) > time()) {
            // Token is valid and not expired

            // Get the user's email from the token data
            $email = $token_data['email'];

            // Proceed with the registration process
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                // Escape user inputs for security
                $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
                $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
                $email = mysqli_real_escape_string($conn, $email);
                $password = mysqli_real_escape_string($conn, $_POST['password']);
                $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
                // Validate input
                if (empty($first_name) || empty($last_name) || empty($password) || empty($confirm_password)) {
                    echo "All fields are required.";
                    exit();
                } elseif ($password != $confirm_password) {
                    echo "Passwords do not match.";
                    exit();
                } else {
                    // Hash the password
                    $password = password_hash($password, PASSWORD_DEFAULT);
                    // Insert user data into the database
                    $sql = "INSERT INTO users (first_name, last_name, email, Pass) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssss", $first_name, $last_name, $email, $password);
                    if ($stmt->execute()) {
                        // Delete the token from the user_tokens table
                        $sql = "DELETE FROM user_tokens WHERE token = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $token);
                        $stmt->execute();
                        echo "Registration successful!";
                        exit();
                    } else {
                        echo "Registration failed. Please try again.";
                        exit();
                    }
                }
            }

            // 


        } else {
            // Token is invalid or expired
            echo "The registration link has expired.";
            exit();
        }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center flex-column" style="min-height: 100vh;">
        <h2>User Registration</h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?token=' . urlencode($token); ?>" method="post" class="container" style="max-width: 500px;">
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo$email; ?>" disabled>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-success w-100 ">Register Now</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</body>
</html>


