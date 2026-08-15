<?php

include("conn.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: browsebook.php");
    exit();
}

$book_id = (int) $_GET['id'];

$stmt = $conn->prepare("
    SELECT 
        books.*,
        users.username,
        users.fullname,
        users.created_at AS user_created_at
    FROM books
    JOIN users
        ON books.user_id = users.id
    WHERE books.id = ?
");

$stmt->bind_param("i", $book_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    header("Location: browsebook.php");
    exit();
}

$book = $result->fetch_assoc();

$stmt->close();

include("header.php");

?>
<link rel="stylesheet" href="css/books.css">

<section class="book-details-section">

    <div class="book-details-container">

        <div class="book-details-image">

            <img src="images/<?php echo htmlspecialchars($book['image']); ?>"
                alt="<?php echo htmlspecialchars($book['title']); ?>">

        </div>

        <div class="book-details-info">

            <span class="book-category">
                <?php echo htmlspecialchars($book['category']); ?>
            </span>

            <h1>
                <?php echo htmlspecialchars($book['title']); ?>
            </h1>

            <p class="book-author">
                By
                <strong>
                    <?php echo htmlspecialchars($book['author']); ?>
                </strong>
            </p>

            <div class="detail-row">

                <strong>Condition:</strong>

                <span>
                    <?php echo htmlspecialchars($book['book_condition']); ?>
                </span>

            </div>

            <div class="detail-row">

                <strong>Listing Type:</strong>

                <span>
                    <?php echo htmlspecialchars($book['type']); ?>
                </span>

            </div>

            <?php if ($book['type'] === 'Sell') { ?>

                <div class="book-price">

                    Rs.
                    <?php echo number_format((float) $book['price'], 2); ?>

                </div>

            <?php } else { ?>

                <div class="exchange-price">

                    Available for Exchange

                </div>

            <?php } ?>

            <div class="book-description">

                <h3>Description</h3>

                <?php if (!empty($book['description'])) { ?>

                    <p>
                        <?php
                        echo nl2br(
                            htmlspecialchars($book['description'])
                        );
                        ?>
                    </p>

                <?php } else { ?>

                    <p class="no-description">
                        No description provided by the seller.
                    </p>

                <?php } ?>

            </div>

            <div class="seller-box">

                <h3>Seller</h3>

                <p>

                    <strong>
                        <?php echo htmlspecialchars($book['fullname']); ?>
                    </strong>

                </p>

                <p>
                    @<?php echo htmlspecialchars($book['username']); ?>
                </p>

            </div>

            <?php if (isset($_SESSION['user_id'])) { ?>

                <?php if ($_SESSION['user_id'] == $book['user_id']) { ?>

                    <a href="profile.php" class="details-btn">
                        Manage Your Listing
                    </a>

                <?php } else { ?>

                    <button type="button" class="details-btn" onclick="alert('Contact feature will be added next.')">
                        Contact Seller
                    </button>

                <?php } ?>

            <?php } else { ?>

                <a href="login.php" class="details-btn">
                    Login to Contact Seller
                </a>

            <?php } ?>

            <a href="browsebook.php" class="back-link">
                ← Back to Browse Books
            </a>

        </div>

    </div>

</section>

<?php

include("footer.php");

?>