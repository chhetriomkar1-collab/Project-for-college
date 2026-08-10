<?php
include("conn.php");

$message = "";

if(isset($_POST['register']))
{
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];

    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users(fullname,username,email,password)
            VALUES('$fullname','$username','$email','$password')";

    if(mysqli_query($conn,$sql))
    {
        $message = "Registration Successful!";
    }
    else
    {
        $message = "Username or Email already exists.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Booked UP</title>
    <link href="css/register.css" rel="stylesheet">
</head>
<body>

<<div class="register-container">

    <div class="register-card">

        <h1>Create Account</h1>
        <p class="subtitle">
            Get Booked UP today
        </p>

        <form method="POST">

            <label>Full Name</label>
            <input class="input1" type="text" name="fullname">

            <label>Username</label>
            <input class="input1" type="text" name="username">

            <label>Email</label>
            <input class="input1" type="email" name="email">

            <label>Password</label>
            <input class="input1" type="password" name="password">

            <label>Confirm Password</label>
            <input class="input1" type="password" name="confirm_password">

            <button class="register-btn" type="submit">
                Create Account
            </button>

        </form>

        <div class="divider">or</div>

        <div class="small-text">
            Already have an account?
        </div>

        <a href="login.php">
            Login Here
        </a>

    </div>

</div>