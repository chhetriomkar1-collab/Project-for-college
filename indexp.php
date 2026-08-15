<?php

include("conn.php");

// Get a few recent book images
$heroBooks = [];

$result = $conn->query("
    SELECT image
    FROM books
    WHERE image IS NOT NULL
    AND image != ''
    ORDER BY created_at DESC
    LIMIT 5
");

if ($result) {

    while ($row = $result->fetch_assoc()) {

        if (file_exists("images/" . $row['image'])) {

            $heroBooks[] =
                "images/" . $row['image'];
        }
    }
}
?>
<?php
include("header.php");
?>

<link rel="stylesheet" href="css/hero.css">
<link rel="stylesheet" href="css/books.css">
<section class="hero">

    <div class="hero-left">

        <span class="hero-tag">
            📚 Nepal's Book Marketplace
        </span>

        <h1>
            Buy, Sell & Exchange <br>
            Books with Students.
        </h1>

        <p>
            Booked UP connects students across Nepal to buy,
            sell, and exchange textbooks, novels, reference books,
            and study materials at affordable prices.
        </p>

        <div class="hero-buttons">

            <a href="browsebook.php" class="btn-primary">
                Browse Books
            </a>

            <?php if (isset($_SESSION['user_id'])) { ?>

                <a href="addbook.php" class="btn-secondary">
                    Sell a Book
                </a>

            <?php } else { ?>

                <a href="login.php" class="btn-secondary">
                    Login to Sell
                </a>

            <?php } ?>

        </div>

    </div>
    <div class="hero-right">

        <?php if (count($heroBooks) > 0) { ?>

            <img id="heroBook" src="<?php echo htmlspecialchars($heroBooks[0]); ?>" alt="Book">

        <?php } else { ?>

            <img id="heroBook" src="images/book.jpg" alt="Books">

        <?php } ?>

    </div>
</section>

<!-- LATEST BOOKS -->

<section class="books-section">

    <div class="section-title">

        <h2>Latest Books</h2>

        <p>
            Recently added books from students
        </p>

    </div>

    <div class="books-grid">

        <?php

        $sql = "SELECT books.*, users.username
                FROM books
                JOIN users
                ON books.user_id = users.id
                ORDER BY books.created_at DESC
                LIMIT 6";

        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {

            while ($row = mysqli_fetch_assoc($result)) {

                ?>

                <div class="book-card">

                    <img src="images/<?php echo htmlspecialchars($row['image']); ?>"
                        alt="<?php echo htmlspecialchars($row['title']); ?>">

                    <div class="book-info">

                        <h3>
                            <?php echo htmlspecialchars($row['title']); ?>
                        </h3>

                        <p>
                            <strong>Author:</strong>
                            <?php echo htmlspecialchars($row['author']); ?>
                        </p>

                        <p>
                            <strong>Category:</strong>
                            <?php echo htmlspecialchars($row['category']); ?>
                        </p>

                        <p>
                            <strong>Condition:</strong>
                            <?php echo htmlspecialchars($row['book_condition']); ?>
                        </p>

                        <?php if ($row['type'] === 'Sell') { ?>

                            <h4>
                                Rs.
                                <?php echo number_format($row['price']); ?>
                            </h4>

                        <?php } else { ?>

                            <h4 style="color:green;">
                                Available for Exchange
                            </h4>

                        <?php } ?>

                        <small>
                            Posted by
                            <?php echo htmlspecialchars($row['username']); ?>
                        </small>

                    </div>

                </div>

                <?php

            }

        } else {

            ?>

            <p class="no-books">
                No books have been listed yet.
            </p>

            <?php

        }

        ?>

    </div>

    <div class="browse-all">

        <a href="browsebook.php" class="btn-primary">
            Browse All Books
        </a>

    </div>

</section>
<script>

    const heroBooks = <?php
    echo json_encode($heroBooks);
    ?>;

    const heroBook =
        document.getElementById("heroBook");

    let currentBook = 0;

    if (heroBooks.length > 1) {

        setInterval(function () {

            currentBook++;

            if (currentBook >= heroBooks.length) {
                currentBook = 0;
            }

            // Fade out
            heroBook.style.opacity = "0";

            setTimeout(function () {

                heroBook.src =
                    heroBooks[currentBook];

                // Fade back in
                heroBook.style.opacity = "1";

            }, 300);

        }, 5000);

    }

</script>

<?php
include("footer.php");
?>