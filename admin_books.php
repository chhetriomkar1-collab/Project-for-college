<?php

session_start();
include("conn.php");

// GET LOGGED-IN USER

$user_id = $_SESSION['user_id']
    ?? $_SESSION['id']
    ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit();
}

// ADMIN CHECK

$adminStmt = $conn->prepare("
    SELECT role
    FROM users
    WHERE id = ?
");

$adminStmt->bind_param(
    "i",
    $user_id
);

$adminStmt->execute();

$adminResult = $adminStmt->get_result();

if ($adminResult->num_rows !== 1) {

    $adminStmt->close();

    session_destroy();

    header("Location: login.php");
    exit();
}
$currentUser = $adminResult->fetch_assoc();

$adminStmt->close();

if (strtolower($currentUser['role']) !== 'admin') {

    header("Location: dashboard.php");
    exit();
}

// DELETE BOOK

if (isset($_POST['delete_book'])) {

    $book_id = (int) $_POST['book_id'];

    $imageStmt = $conn->prepare("
        SELECT image
        FROM books
        WHERE id = ?
    ");

    $imageStmt->bind_param(
        "i",
        $book_id
    );

    $imageStmt->execute();

    $imageResult = $imageStmt->get_result();

    $bookImage = "";

    if ($imageResult->num_rows === 1) {

        $imageRow = $imageResult->fetch_assoc();

        $bookImage = $imageRow['image'];
    }

    $imageStmt->close();

    $deleteStmt = $conn->prepare("
        DELETE FROM books
        WHERE id = ?
    ");

    $deleteStmt->bind_param(
        "i",
        $book_id
    );

    $deleteStmt->execute();

    $deleteStmt->close();
    if (!empty($bookImage)) {

        $imagePath =
            "images/" . $bookImage;

        if (file_exists($imagePath)) {

            unlink($imagePath);
        }
    }

    header("Location: admin_books.php");

    exit();
}

$search = trim($_GET['search'] ?? "");

if ($search !== "") {

    $searchValue = "%" . $search . "%";

    $booksStmt = $conn->prepare("
        SELECT
            books.id,
            books.title,
            books.author,
            books.category,
            books.book_condition,
            books.type,
            books.price,
            books.image,
            books.created_at,
            users.username,
            users.fullname
        FROM books
        JOIN users
            ON books.user_id = users.id
        WHERE
            books.title LIKE ?
            OR books.author LIKE ?
            OR books.category LIKE ?
            OR users.username LIKE ?
        ORDER BY books.created_at DESC
    ");

    $booksStmt->bind_param(
        "ssss",
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue
    );

} else {

    $booksStmt = $conn->prepare("
        SELECT
            books.id,
            books.title,
            books.author,
            books.category,
            books.book_condition,
            books.type,
            books.price,
            books.image,
            books.created_at,
            users.username,
            users.fullname
        FROM books
        JOIN users
            ON books.user_id = users.id
        ORDER BY books.created_at DESC
    ");
}

$booksStmt->execute();

$books = $booksStmt->get_result();

include("header.php");

?>
<link rel="stylesheet" href="css/admin.css">

<section class="admin-management-section">

    <div class="admin-management-container">

        <!-- HEADER -->

        <div class="admin-page-header">

            <div>

                <span class="admin-page-badge">
                    <i class="fa-solid fa-shield-halved"></i>
                    Administrator
                </span>

                <h1>
                    Manage Books
                </h1>

                <p>
                    Review and manage marketplace listings.
                </p>

            </div>

            <a href="dashboard.php" class="back-dashboard-btn">
                <i class="fa-solid fa-arrow-left"></i>
                Dashboard
            </a>

        </div>

        <div class="admin-search-panel">

            <form method="GET">

                <div class="admin-search-box">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input type="text" name="search" placeholder="Search books or sellers..."
                        value="<?php echo htmlspecialchars($search); ?>">

                </div>

                <button type="submit">
                    Search
                </button>

                <?php if ($search !== "") { ?>

                    <a href="admin_books.php">
                        Clear
                    </a>

                <?php } ?>

            </form>

        </div>
        <div class="admin-table-panel">

            <div class="admin-table-header">

                <h2>
                    Marketplace Listings
                </h2>

                <span>
                    <?php echo $books->num_rows; ?> result(s)
                </span>

            </div>

            <div class="admin-table-wrapper">

                <table class="admin-management-table">

                    <thead>
                        <tr>
                            <th>
                                Book
                            </th>
                            <th>
                                Seller
                            </th>
                            <th>
                                Category
                            </th>
                            <th>
                                Condition
                            </th>
                            <th>
                                Type
                            </th>
                            <th>
                                Price
                            </th>
                            <th>
                                Listed
                            </th>
                            <th>
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($books->num_rows > 0) { ?>
                            <?php while ($book = $books->fetch_assoc()) { ?>
                                <tr>
                                    <td>
                                        <div class="admin-book-info">
                                            <img src="images/<?php echo htmlspecialchars($book['image']); ?>" alt="Book">
                                            <div>
                                                <strong>
                                                    <?php
                                                    echo htmlspecialchars(
                                                        $book['title']
                                                    );
                                                    ?>
                                                </strong>

                                                <span>
                                                    <?php
                                                    echo htmlspecialchars(
                                                        $book['author']
                                                    );
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @<?php
                                        echo htmlspecialchars(
                                            $book['username']
                                        );
                                        ?>
                                    </td>
                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $book['category']
                                        );
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $book['book_condition']
                                        );
                                        ?>

                                    </td>
                                    <td>
                                        <span class="
                                        listing-type-badge
                                        <?php
                                        echo $book['type'] === 'Sell'
                                            ? 'sell-badge'
                                            : 'exchange-badge';
                                        ?>
                                    ">

                                            <?php
                                            echo htmlspecialchars(
                                                $book['type']
                                            );
                                            ?>

                                        </span>

                                    </td>
                                    <td>

                                        <?php if ($book['type'] === 'Sell') { ?>

                                            Rs.
                                            <?php
                                            echo number_format(
                                                (float) $book['price'],
                                                2
                                            );
                                            ?>

                                        <?php } else { ?>

                                            —

                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php
                                        echo date(
                                            "M d, Y",
                                            strtotime(
                                                $book['created_at']
                                            )
                                        );
                                        ?>
                                    </td>
                                    <td>
                                        <div class="book-admin-actions">

                                            <a href="bookdetails.php?id=<?php echo (int) $book['id']; ?>"
                                                class="view-admin-book-btn">
                                                View
                                            </a>
                                            <form method="POST" onsubmit="return confirm('Delete this listing permanently?');">
                                                <input type="hidden" name="book_id" value="<?php echo (int) $book['id']; ?>">
                                                <button type="submit" name="delete_book" class="delete-admin-book-btn">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="8" class="no-users">
                                    No books found.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php
$booksStmt->close();
include("footer.php");
?>