<?php

session_start();

include("conn.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
// FETCH USER

$stmt = $conn->prepare("
    SELECT
        id,
        fullname,
        username,
        email,
        profile_image,
        role,
        created_at
    FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$userResult = $stmt->get_result();

if ($userResult->num_rows !== 1) {
    $stmt->close();
    session_destroy();

    header("Location: login.php");
    exit();
}

$user = $userResult->fetch_assoc();

$stmt->close();

// DELETE BOOK

if (isset($_POST['delete_book'])) {

    $book_id = (int) $_POST['book_id'];

    // First get image name
    $imageStmt = $conn->prepare("
        SELECT image
        FROM books
        WHERE id = ?
        AND user_id = ?
    ");

    $imageStmt->bind_param(
        "ii",
        $book_id,
        $user_id
    );

    $imageStmt->execute();

    $imageResult = $imageStmt->get_result();

    if ($imageResult->num_rows === 1) {

        $book = $imageResult->fetch_assoc();

        // Delete database record
        $deleteStmt = $conn->prepare("
            DELETE FROM books
            WHERE id = ?
            AND user_id = ?
        ");

        $deleteStmt->bind_param(
            "ii",
            $book_id,
            $user_id
        );

        $deleteStmt->execute();

        $deleteStmt->close();

        // Delete image from server
        if (!empty($book['image'])) {

            $imagePath = "images/" . $book['image'];

            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
    }

    $imageStmt->close();

    header("Location: profile.php");
    exit();
}

// FETCH USER'S BOOKS

$bookStmt = $conn->prepare("
    SELECT *
    FROM books
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$bookStmt->bind_param(
    "i",
    $user_id
);

$bookStmt->execute();

$booksResult = $bookStmt->get_result();

// HEADER

include("header.php");

?>
<link rel="stylesheet" href="css/profile.css">

<section class="profile-section">

    <div class="profile-container">

        <!-- =================================
             PROFILE CARD
        ================================== -->

        <div class="profile-card">

            <div class="profile-avatar">

                <?php if (!empty($user['profile_image'])) { ?>

                    <img src="uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image">

                <?php } else { ?>

                    <div class="default-avatar">

                        <i class="fa-solid fa-user"></i>

                    </div>

                <?php } ?>

            </div>

            <h1>
                <?php echo htmlspecialchars($user['fullname']); ?>
            </h1>

            <p class="profile-username">

                @<?php echo htmlspecialchars($user['username']); ?>

            </p>

            <p class="profile-email">

                <?php echo htmlspecialchars($user['email']); ?>

            </p>

            <span class="profile-role">

                <?php echo htmlspecialchars(ucfirst($user['role'])); ?>

            </span>

            <p class="member-since">

                Member since
                <?php
                echo date(
                    "F Y",
                    strtotime($user['created_at'])
                );
                ?>

            </p>

            <a href="editprofile.php" class="edit-profile-btn">
                Edit Profile
            </a>

        </div>

        <!-- =================================
             MY LISTINGS
        ================================== -->

        <div class="my-listings">

            <div class="profile-section-title">

                <div>

                    <h2>
                        My Listings
                    </h2>

                    <p>
                        Books you have listed on Booked UP
                    </p>

                </div>

                <a href="addbook.php" class="add-listing-btn">
                    <i class="fa-solid fa-plus"></i>
                    Add Book
                </a>

            </div>

            <div class="my-books-grid">

                <?php

                if ($booksResult->num_rows > 0) {

                    while ($book = $booksResult->fetch_assoc()) {

                        ?>

                        <div class="my-book-card">

                            <!-- IMAGE -->

                            <img src="images/<?php echo htmlspecialchars($book['image']); ?>"
                                alt="<?php echo htmlspecialchars($book['title']); ?>">

                            <div class="my-book-info">

                                <h3>

                                    <?php
                                    echo htmlspecialchars(
                                        $book['title']
                                    );
                                    ?>

                                </h3>

                                <p>

                                    <strong>Author:</strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $book['author']
                                    );
                                    ?>

                                </p>

                                <p>

                                    <strong>Category:</strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $book['category']
                                    );
                                    ?>

                                </p>

                                <?php if ($book['type'] === "Sell") { ?>

                                    <h4>

                                        Rs.
                                        <?php
                                        echo number_format(
                                            (float) $book['price'],
                                            2
                                        );
                                        ?>

                                    </h4>

                                <?php } else { ?>

                                    <h4 class="exchange-text">

                                        Exchange

                                    </h4>

                                <?php } ?>

                                <div class="listing-actions">

                                    <a href="bookdetails.php?id=<?php echo (int) $book['id']; ?>" class="view-listing-btn">
                                        View
                                    </a>

                                    <a href="editbook.php?id=<?php echo (int) $book['id']; ?>" class="edit-listing-btn">
                                        Edit
                                    </a>

                                    <form method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this book?');">

                                        <input type="hidden" name="book_id" value="<?php echo (int) $book['id']; ?>">

                                        <button type="submit" name="delete_book" class="delete-listing-btn">
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </div>

                        </div>

                        <?php

                    }

                } else {

                    ?>

                    <div class="empty-listings">

                        <i class="fa-solid fa-book-open"></i>

                        <h3>
                            You haven't listed any books yet.
                        </h3>

                        <p>
                            Sell or exchange your books with other students.
                        </p>

                        <a href="addbook.php" class="btn-primary">
                            List Your First Book
                        </a>

                    </div>

                    <?php

                }

                ?>

            </div>

        </div>

    </div>

</section>

<?php

$bookStmt->close();

include("footer.php");

?>