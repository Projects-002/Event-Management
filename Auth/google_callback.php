<?php
require '../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Dotenv\Dotenv;

session_start();

if (!isset($_GET["code"])) {
    exit("Login failed");
}

$dotenv = Dotenv::createImmutable('../');
$dotenv->load();

$client = new Client();
$apiUrl = 'https://oauth2.googleapis.com/token';

$data = [
    'client_id' => $_ENV['GOOGLE_CLIENT_ID'],
    'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'],
    'code' => $_GET['code'],
    'grant_type' => 'authorization_code',
    'redirect_uri' => $_ENV['GOOGLE_REDIRECT_URI'],
];

try {
    $response = $client->post($apiUrl, [
        'form_params' => $data,
        'headers' => [
            'Accept' => 'application/json'
        ]
    ]);

    if ($response->getStatusCode() == 200) {
        $tokenData = json_decode($response->getBody()->getContents(), true);
        $accessToken = $tokenData['access_token'];

        // Use the access token to get user information
        $userResponse = $client->get('https://www.googleapis.com/oauth2/v1/userinfo?alt=json', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept' => 'application/json'
            ]
        ]);

        if ($userResponse->getStatusCode() == 200) {
            $userinfo = json_decode($userResponse->getBody()->getContents(), true);
            $givenName = htmlspecialchars($userinfo['given_name'], ENT_QUOTES, 'UTF-8');
            $familyName = htmlspecialchars($userinfo['family_name'], ENT_QUOTES, 'UTF-8');
            $email = htmlspecialchars($userinfo['email'], ENT_QUOTES, 'UTF-8');
            $picture = htmlspecialchars($userinfo['picture'], ENT_QUOTES, 'UTF-8');

            require '../Database/db.php';
            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            $check = "SELECT * FROM users WHERE Email = '$email'";
            $check_query = mysqli_query($conn, $check);
            $row = mysqli_fetch_assoc($check_query);

            if (mysqli_num_rows($check_query) > 0) {
                $id = $row['SN'];
                $_SESSION['google_auth'] = $id;
                header("Location: http://localhost/projects/Event_Management/eventcalendar/index.php");
            } else {
                // Insert user into database
                $insert = "INSERT INTO users (First_Name, Last_Name, Email, Avatar, Pass, Reg_Date) VALUES ('$givenName', '$familyName', '$email', '$picture', 'portfolio1234', NOW())";
                $insert_query = mysqli_query($conn, $insert);

                if ($insert_query) {
                    $id = mysqli_insert_id($conn);
                    $_SESSION['google_auth'] = $id;
                    header("Location: http://localhost/projects/Event_Management/eventcalendar/index.php");
                } else {
                    exit("Database insertion failed");
                }
            }
        } else {
            exit("Failed to get user information");
        }
    } else {
        exit("Failed to get access token");
    }
} catch (RequestException $e) {
    exit('Request Exception: ' . $e->getMessage());
}
