<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Second Pedantry</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<nav class="navbar">

    <div class="logo" onclick="window.location.href='indexp.php';" style="cursor: pointer;">
        <i class="fa-solid fa-book-open"></i>
        <span>Booked UP</span>
    </div>

    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input
            type="text"
            placeholder="Search books...">
    </div>

    <div class="nav-links">
        <a href="indexp.php" onclick="window.location.href='indexp.php';" style="cursor: pointer;">
            <i class="fa-solid fa-house"></i>
            Home
        </a>

        <a href="addbook.php" onclick="window.location.href='addbook.php';" style="cursor: pointer;">
            <i class="fa-solid fa-plus"></i>
            Sell Book
        </a>

        <a href="profile.php" onclick="window.location.href='profile.php';" style="cursor: pointer;">
            <i class="fa-solid fa-user"></i>
            Profile
        </a>

        <a href="logout.php" class="logout" onclick="window.location.href='logout.php';" style="cursor: pointer;">
            Logout
        </a>
    </div>

</nav>