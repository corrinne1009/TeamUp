<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture user details from the signup form
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT); // Secure password hashing

    // Save user data in a file (or database later)
    $file = fopen("users.txt", "a");
    fwrite($file, "$name, $email, $password\n");
    fclose($file);

    // Redirect new users to the profile setup page
    header("Location: profile.html");
    exit();
}
?>
