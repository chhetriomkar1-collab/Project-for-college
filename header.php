<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("conn.php");

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Booked UP</title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/hero.css">
  <link rel="stylesheet" href="css/navbar.css">
</head>

<body>

<nav class="navbar">

    <!-- LOGO -->

    <div
        class="logo"
        onclick="window.location.href='indexp.php';"
        style="cursor:pointer;"
    >

        <i class="fa-solid fa-book-open"></i>

        <span>Booked UP</span>

    </div>


    <!-- SEARCH -->
<form
    action="browsebook.php"
    method="GET"
    class="search-box"
>

    <i class="fa-solid fa-magnifying-glass"></i>

    <input
        type="text"
        name="search"
        placeholder="Search books..."
        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
    >

</form>


    <!-- NAVIGATION -->

    <div class="nav-links">

        <a href="indexp.php">

            <i class="fa-solid fa-house"></i>

            Home

        </a>


        <a href="browsebook.php">

            <i class="fa-solid fa-book"></i>

            Browse

        </a>


        <?php if (isset($_SESSION['user_id'])) { ?>

            <a href="addbook.php">

                <i class="fa-solid fa-plus"></i>

                Sell Book

            </a>


            <a href="profile.php">

                <?php

                $profileImage = "default-profile.png";

                $user_id = $_SESSION['user_id'];

                $stmt = $conn->prepare(
                    "SELECT profile_image FROM users WHERE id = ?"
                );

                $stmt->bind_param("i", $user_id);

                $stmt->execute();

                $result = $stmt->get_result();

               if ($result->num_rows === 1) {

    $headerUser = $result->fetch_assoc();

    if (!empty($headerUser['profile_image'])) {

        $profileImage =
            "uploads/" .
            $headerUser['profile_image'];
    }
}
                $stmt->close();

                ?>

                <img
                    src="<?php echo htmlspecialchars($profileImage); ?>"
                    alt="Profile"
                    class="profile-image"
                >

                Profile

            </a>


            <a
                href="logout.php"
                class="logout"
            >
                Logout
            </a>
            <a href="dashboard.php">

    <i class="fa-solid fa-chart-line"></i>

    Dashboard

</a>

        <?php } else { ?>

            <a href="login.php">

                <i class="fa-solid fa-right-to-bracket"></i>

                Login

            </a>

        <?php } ?>

    </div>

</nav>