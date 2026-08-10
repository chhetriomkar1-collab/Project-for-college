<?php
session_start();
include("conn.php");

$error = "";

if(isset($_POST['login']))
{
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s",$username);
    $stmt->execute();

    $result = $stmt->get_result();

    if($result->num_rows > 0)
    {
        $user = $result->fetch_assoc();

        if(password_verify($password, $user['password']))
        {
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            header("Location: indexp.php");
            exit();
        }
        else
        {
            $error = "Incorrect password.";
        }
    }
    else
    {
        $error = "Username not found.";
    }
}
?>
<html>
    <head>
        <link rel="stylesheet" href="css/login.css">
        <title>
           Booked UP - Login
        </title>
    </head>
<body>
    <div class="login-container">

    <div class="login-card">

        <h1>Booked UP</h1>
        <p class="subtitle">Buy • Sell • Exchange Books</p>

        <?php if($error != ""){ ?>
            <p class="error"><?php echo $error; ?></p>
        <?php } ?>

        <form method="POST">

            <label>Username</label>
            <input class="input1" type="text" name="username" required>

            <label>Password</label>
            <input class="input1" type="password" name="password" required>

            <div class="remember">
                <input type="checkbox" name="remember">
                <span>Remember Me</span>
            </div>

            <button class="logger" type="submit" name="login">
                Login
            </button>

        </form>

        <a href="#">Forgot Password?</a>
        <a href="registerp.php">Create New Account</a>

    </div>

</div>
    </body>
</html>