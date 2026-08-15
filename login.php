<?php

session_start();

include("conn.php");

$error = "";

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {

        $error = "Please enter username and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT * FROM users WHERE username = ?"
        );

        $stmt->bind_param("s", $username);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {

                $_SESSION['id'] = $user['id'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $user['role'];

                header("Location: indexp.php");
                exit();

            } else {

                $error = "Incorrect password.";
            }

        } else {

            $error = "Username not found.";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="css/login.css">

    <title>Booked UP - Login</title>

</head>

<body>

    <div class="login-container">

        <div class="login-card">

            <h1>Booked UP</h1>

            <p class="subtitle">
                Buy • Sell • Exchange Books
            </p>

            <?php if ($error != "") { ?>

                <p class="error">
                    <?php echo htmlspecialchars($error); ?>
                </p>

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

            <a href="#">
                Forgot Password?
            </a>

            <a href="registerp.php">
                Create New Account
            </a>

        </div>

    </div>

</body>

</html>