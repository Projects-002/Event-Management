<?php

// Check URL for token
if (!isset($_GET['token'])) {
    echo "Token not found.";
    exit();
} else {
    // Include the database configuration file
    require_once '../database/db.php';
    // Get the token from the URL
    $token = $_GET['token'];
    // Check if token is in database
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

        // Proceed with the password reset process
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // Escape user inputs for security
            $password = mysqli_real_escape_string($conn, $_POST['password']);
            $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
            // Validate input
            if (empty($password) || empty($confirm_password)) {
                echo "All fields are required.";
                exit();
            } elseif ($password != $confirm_password) {
                echo "Passwords do not match.";
                exit();
            } else {
                // Hash the password
                $password = password_hash($password, PASSWORD_DEFAULT);
                // Update user password in the database
                $sql = "UPDATE users SET Pass = ? WHERE email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $password, $email);
                if ($stmt->execute()) {
                    // Delete the token from the user_tokens table
                    $sql = "DELETE FROM user_tokens WHERE token = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $token);
                    $stmt->execute();
                    echo "Password reset successful!";
                    exit();
                } else {
                    echo "Password reset failed. Please try again.";
                    exit();
                }
            }
        }
    } else {
        // Token is invalid or expired
        echo "The password reset link has expired.";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center flex-column" style="min-height: 100vh;">
        <h2>Reset Password</h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?token=' . urlencode($token); ?>" method="post" class="container" style="max-width: 500px;">
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Reset Password</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>