<?php
session_start();
include("conn.php");

$message = "";
$messageType = "";

if (isset($_POST['register'])) {

    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check required fields
    if (
        empty($fullname) ||
        empty($username) ||
        empty($email) ||
        empty($password) ||
        empty($confirm_password)
    ) {
        $message = "Please fill in all fields.";
        $messageType = "error";
    }

    // Check password match
    elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $messageType = "error";
    }

    // Check password length
    elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
        $messageType = "error";
    }

    else {

        // Check whether username or email already exists
        $check = $conn->prepare(
            "SELECT id FROM users WHERE username = ? OR email = ?"
        );

        $check->bind_param("ss", $username, $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Username or email already exists.";
            $messageType = "error";

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare(
                "INSERT INTO users
                (fullname, username, email, password, role)
                VALUES (?, ?, ?, ?, 'user')"
            );

            $stmt->bind_param(
                "ssss",
                $fullname,
                $username,
                $email,
                $hashedPassword
            );

            if ($stmt->execute()) {

                $message = "Registration successful! You can now login.";
                $messageType = "success";

            } else {

                $message = "Registration failed. Please try again.";
                $messageType = "error";
            }

            $stmt->close();
        }

        $check->close();
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

<div class="register-container">

    <div class="register-card">

        <h1>Create Account</h1>

        <p class="subtitle">
            Get Booked UP today
        </p>

        <?php if ($message != "") { ?>

            <p class="<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>

        <?php } ?>

        <form method="POST">

            <label>Full Name</label>

            <input
                class="input1"
                type="text"
                name="fullname"
                required
            >

            <label>Username</label>

            <input
                class="input1"
                type="text"
                name="username"
                required
            >

            <label>Email</label>

            <input
                class="input1"
                type="email"
                name="email"
                required
            >

            <label>Password</label>

            <input
                class="input1"
                type="password"
                name="password"
                required
            >

            <label>Confirm Password</label>

            <input
                class="input1"
                type="password"
                name="confirm_password"
                required
            >

            <button
                class="register-btn"
                type="submit"
                name="register"
            >
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

</body>

</html>