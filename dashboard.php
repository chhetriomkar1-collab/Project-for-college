<?php

session_start();
include("conn.php");

// LOGIN CHECK

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// GET CURRENT USER

$stmt = $conn->prepare("
    SELECT
        id,
        fullname,
        username,
        email,
        role,
        profile_image,
        created_at
    FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    session_destroy();

    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();

$stmt->close();

// ROLE

$role = strtolower($user['role']);

// USER DASHBOARD STATISTICS

$myBooksStmt = $conn->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(type = 'Sell') AS sell_count,
        SUM(type = 'Exchange') AS exchange_count
    FROM books
    WHERE user_id = ?
");

$myBooksStmt->bind_param("i", $user_id);
$myBooksStmt->execute();

$myBooks = $myBooksStmt->get_result()->fetch_assoc();

$myBooksStmt->close();

// ADMIN STATISTICS

$adminStats = [];

if ($role === 'admin') {

    $statsQuery = $conn->query("
        SELECT

            (SELECT COUNT(*) FROM users)
            AS total_users,

            (SELECT COUNT(*) FROM books)
            AS total_books,

            (SELECT COUNT(*) FROM books WHERE type = 'Sell')
            AS total_sell,

            (SELECT COUNT(*) FROM books WHERE type = 'Exchange')
            AS total_exchange
    ");

    $adminStats = $statsQuery->fetch_assoc();
}

// RECENT USER LISTINGS

$recentBooksStmt = $conn->prepare("
    SELECT
        id,
        title,
        author,
        category,
        type,
        price,
        image,
        created_at
    FROM books
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 5
");

$recentBooksStmt->bind_param("i", $user_id);
$recentBooksStmt->execute();

$recentBooks = $recentBooksStmt->get_result();

// ADMIN RECENT USERS

$recentUsers = null;
$recentAdminBooks = null;

if ($role === 'admin') {

    $recentUsers = $conn->query("
        SELECT
            id,
            fullname,
            username,
            email,
            role,
            created_at
        FROM users
        ORDER BY created_at DESC
        LIMIT 5
    ");

    $recentAdminBooks = $conn->query("
        SELECT
            books.id,
            books.title,
            books.type,
            books.price,
            books.created_at,
            users.username
        FROM books
        JOIN users
            ON books.user_id = users.id
        ORDER BY books.created_at DESC
        LIMIT 5
    ");
}

include("header.php");

?>
<link rel="stylesheet" href="css/dashboard.css">

<section class="dashboard-section">

    <div class="dashboard-container">

        <!-- =================================
             HEADER
        ================================== -->

        <div class="dashboard-header">

            <div>

                <?php if ($role === 'admin') { ?>

                    <span class="dashboard-badge admin-badge">
                        <i class="fa-solid fa-shield-halved"></i>
                        Administrator
                    </span>

                    <h1>
                        Admin Dashboard
                    </h1>

                    <p>
                        Manage Booked UP and monitor the marketplace.
                    </p>

                <?php } else { ?>

                    <span class="dashboard-badge user-badge">
                        <i class="fa-solid fa-user"></i>
                        User Dashboard
                    </span>

                    <h1>
                        Welcome,
                        <?php echo htmlspecialchars($user['fullname']); ?>!
                    </h1>

                    <p>
                        Manage your Booked UP account and listings.
                    </p>

                <?php } ?>

            </div>

            <a href="profile.php" class="dashboard-profile-btn">
                <i class="fa-solid fa-user"></i>
                My Profile
            </a>

        </div>

        <?php if ($role === 'admin') { ?>

            <!-- =================================
                 ADMIN DASHBOARD
            ================================== -->

            <div class="dashboard-stats">

                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>

                    <div>

                        <span>
                            Total Users
                        </span>

                        <strong>
                            <?php
                            echo number_format(
                                $adminStats['total_users']
                            );
                            ?>
                        </strong>

                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="fa-solid fa-book"></i>
                    </div>

                    <div>

                        <span>
                            Total Books
                        </span>

                        <strong>
                            <?php
                            echo number_format(
                                $adminStats['total_books']
                            );
                            ?>
                        </strong>

                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="fa-solid fa-tag"></i>
                    </div>

                    <div>

                        <span>
                            Sell Listings
                        </span>

                        <strong>
                            <?php
                            echo number_format(
                                $adminStats['total_sell']
                            );
                            ?>
                        </strong>

                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="fa-solid fa-right-left"></i>
                    </div>

                    <div>

                        <span>
                            Exchange Listings
                        </span>

                        <strong>
                            <?php
                            echo number_format(
                                $adminStats['total_exchange']
                            );
                            ?>
                        </strong>

                    </div>

                </div>

            </div>

            <!-- ADMIN QUICK ACTIONS -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Administration
                        </h2>

                        <p>
                            Manage the Booked UP marketplace.
                        </p>

                    </div>

                </div>

                <div class="admin-actions">

                    <a href="admin_users.php" class="admin-action-card">

                        <i class="fa-solid fa-users"></i>

                        <div>

                            <strong>
                                Manage Users
                            </strong>

                            <span>
                                View and manage registered users
                            </span>

                        </div>

                    </a>

                    <a href="admin_books.php" class="admin-action-card">

                        <i class="fa-solid fa-book"></i>

                        <div>

                            <strong>
                                Manage Books
                            </strong>

                            <span>
                                Review and manage listings
                            </span>

                        </div>

                    </a>

                    <a href="browsebook.php" class="admin-action-card">

                        <i class="fa-solid fa-store"></i>

                        <div>

                            <strong>
                                View Marketplace
                            </strong>

                            <span>
                                Browse the public marketplace
                            </span>

                        </div>

                    </a>

                </div>

            </div>

            <!-- RECENT USERS -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Recent Users
                        </h2>

                    </div>

                    <a href="admin_users.php">
                        View All
                    </a>

                </div>

                <div class="dashboard-table-wrapper">

                    <table class="dashboard-table">

                        <thead>

                            <tr>

                                <th>
                                    User
                                </th>

                                <th>
                                    Username
                                </th>

                                <th>
                                    Role
                                </th>

                                <th>
                                    Joined
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php while ($recentUser = $recentUsers->fetch_assoc()) { ?>

                                <tr>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $recentUser['fullname']
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        @<?php
                                        echo htmlspecialchars(
                                            $recentUser['username']
                                        );
                                        ?>
                                    </td>

                                    <td>

                                        <span class="
                                        table-role
                                        <?php
                                        echo $recentUser['role'] === 'admin'
                                            ? 'role-admin'
                                            : 'role-user';
                                        ?>
                                    ">

                                            <?php
                                            echo htmlspecialchars(
                                                ucfirst(
                                                    $recentUser['role']
                                                )
                                            );
                                            ?>

                                        </span>

                                    </td>

                                    <td>
                                        <?php
                                        echo date(
                                            "M d, Y",
                                            strtotime(
                                                $recentUser['created_at']
                                            )
                                        );
                                        ?>
                                    </td>

                                </tr>

                            <?php } ?>

                        </tbody>

                    </table>

                </div>

            </div>

            <!-- RECENT BOOKS -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Recent Listings
                        </h2>

                    </div>

                    <a href="admin_books.php">
                        View All
                    </a>

                </div>

                <div class="dashboard-table-wrapper">

                    <table class="dashboard-table">

                        <thead>

                            <tr>

                                <th>
                                    Book
                                </th>

                                <th>
                                    Seller
                                </th>

                                <th>
                                    Type
                                </th>

                                <th>
                                    Price
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php while ($adminBook = $recentAdminBooks->fetch_assoc()) { ?>

                                <tr>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $adminBook['title']
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        @<?php
                                        echo htmlspecialchars(
                                            $adminBook['username']
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $adminBook['type']
                                        );
                                        ?>
                                    </td>

                                    <td>

                                        <?php if ($adminBook['type'] === 'Sell') { ?>

                                            Rs.
                                            <?php
                                            echo number_format(
                                                (float) $adminBook['price'],
                                                2
                                            );
                                            ?>

                                        <?php } else { ?>

                                            Exchange

                                        <?php } ?>

                                    </td>

                                </tr>

                            <?php } ?>

                        </tbody>

                    </table>

                </div>

            </div>

        <?php } else { ?>

            <!-- =================================
                 USER DASHBOARD
            ================================== -->

            <div class="dashboard-stats">

                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="fa-solid fa-book"></i>
                    </div>

                    <div>

                        <span>
                            My Listings
                        </span>

                        <strong>
                            <?php
                            echo number_format(
                                (int) ($myBooks['total'] ?? 0)
                            );
                            ?>
                        </strong>

                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="fa-solid fa-tag"></i>
                    </div>

                    <div>

                        <span>
                            For Sale
                        </span>

                        <strong>
                            <?php
                            echo number_format(
                                (int) ($myBooks['sell_count'] ?? 0)
                            );
                            ?>
                        </strong>

                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="fa-solid fa-right-left"></i>
                    </div>

                    <div>

                        <span>
                            For Exchange
                        </span>

                        <strong>
                            <?php
                            echo number_format(
                                (int) ($myBooks['exchange_count'] ?? 0)
                            );
                            ?>
                        </strong>

                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <div>

                        <span>
                            Account
                        </span>

                        <strong>
                            User
                        </strong>

                    </div>

                </div>

            </div>

            <!-- USER QUICK ACTIONS -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Quick Actions
                        </h2>

                        <p>
                            Manage your Booked UP account.
                        </p>

                    </div>

                </div>

                <div class="user-actions">

                    <a href="addbook.php" class="user-action-card">

                        <i class="fa-solid fa-plus"></i>

                        <div>

                            <strong>
                                Sell / Exchange a Book
                            </strong>

                            <span>
                                Create a new listing
                            </span>

                        </div>

                    </a>

                    <a href="mylistings.php" class="user-action-card">

                        <i class="fa-solid fa-book"></i>

                        <div>

                            <strong>
                                My Listings
                            </strong>

                            <span>
                                serach edit and manage your listings
                            </span>

                        </div>

                    </a>

                    <a href="editprofile.php" class="user-action-card">

                        <i class="fa-solid fa-user-pen"></i>

                        <div>

                            <strong>
                                Edit Profile
                            </strong>

                            <span>
                                Update your account information
                            </span>

                        </div>

                    </a>

                    <a href="browsebook.php" class="user-action-card">

                        <i class="fa-solid fa-magnifying-glass"></i>

                        <div>

                            <strong>
                                Browse Books
                            </strong>

                            <span>
                                Find books from other users
                            </span>

                        </div>

                    </a>

                </div>

            </div>

            <!-- MY RECENT LISTINGS -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            My Recent Listings
                        </h2>

                    </div>

                    <a href="mylistings.php">
                        View All
                    </a>

                </div>

                <?php if ($recentBooks->num_rows > 0) { ?>

                    <div class="dashboard-book-list">

                        <?php while ($book = $recentBooks->fetch_assoc()) { ?>

                            <div class="dashboard-book">

                                <img src="images/<?php echo htmlspecialchars($book['image']); ?>" alt="Book">

                                <div>

                                    <h3>
                                        <?php
                                        echo htmlspecialchars(
                                            $book['title']
                                        );
                                        ?>
                                    </h3>

                                    <p>
                                        <?php
                                        echo htmlspecialchars(
                                            $book['author']
                                        );
                                        ?>
                                    </p>

                                    <span>
                                        <?php
                                        echo htmlspecialchars(
                                            $book['type']
                                        );
                                        ?>
                                    </span>

                                </div>

                                <a href="bookdetails.php?id=<?php echo (int) $book['id']; ?>">
                                    View
                                </a>

                            </div>

                        <?php } ?>

                    </div>

                <?php } else { ?>

                    <div class="dashboard-empty">

                        <i class="fa-solid fa-book-open"></i>

                        <h3>
                            No listings yet
                        </h3>

                        <p>
                            Start by listing your first book.
                        </p>

                        <a href="addbook.php" class="btn-primary">
                            Add Book
                        </a>

                    </div>

                <?php } ?>

            </div>

        <?php } ?>

    </div>

</section>

<?php

$recentBooksStmt->close();

include("footer.php");

?>