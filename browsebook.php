<?php

include("conn.php");
include("header.php");

// GET SEARCH AND FILTER VALUES

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : "";

$type = isset($_GET['type'])
    ? trim($_GET['type'])
    : "";

$condition = isset($_GET['condition'])
    ? trim($_GET['condition'])
    : "";

$category = isset($_GET['category'])
    ? trim($_GET['category'])
    : "";

// BUILD QUERY

$sql = "
    SELECT
        books.*,
        users.username
    FROM books
    JOIN users
        ON books.user_id = users.id
    WHERE 1=1
";

// SEARCH

if ($search !== "") {

    $searchSafe = mysqli_real_escape_string(
        $conn,
        $search
    );

    $sql .= "
        AND (
            books.title LIKE '%$searchSafe%'
            OR books.author LIKE '%$searchSafe%'
            OR books.category LIKE '%$searchSafe%'
            OR users.username LIKE '%$searchSafe%'
        )
    ";
}

// TYPE FILTER

if ($type === "Sell" || $type === "Exchange") {

    $typeSafe = mysqli_real_escape_string(
        $conn,
        $type
    );

    $sql .= "
        AND books.type = '$typeSafe'
    ";
}

// CONDITION FILTER

$allowedConditions = [
    "New",
    "Like New",
    "Good",
    "Fair",
    "Poor"
];

if (in_array($condition, $allowedConditions)) {

    $conditionSafe = mysqli_real_escape_string(
        $conn,
        $condition
    );

    $sql .= "
        AND books.book_condition = '$conditionSafe'
    ";
}

// CATEGORY FILTER

if ($category !== "") {

    $categorySafe = mysqli_real_escape_string(
        $conn,
        $category
    );

    $sql .= "
        AND books.category = '$categorySafe'
    ";
}

// SORT

$sql .= "
    ORDER BY books.created_at DESC
";

$result = mysqli_query($conn, $sql);

?>
<link rel="stylesheet" href="css/books.css">

<section class="books-section">

    <div class="section-title">

        <h2>Browse Books</h2>

        <p>
            Find textbooks, novels and study materials from students
        </p>

    </div>

    <!-- =====================================
         FILTERS
    ====================================== -->

    <form method="GET" action="browsebook.php" class="book-filters">

        <!-- Keep search when filtering -->

        <input type="text" name="search" placeholder="Search books..." value="<?php echo htmlspecialchars($search); ?>">

        <!-- TYPE -->

        <select name="type">

            <option value="">
                All Types
            </option>

            <option value="Sell" <?php echo ($type === "Sell") ? "selected" : ""; ?>> Sell </option>

            <option value="Exchange" <?php echo ($type === "Exchange") ? "selected" : ""; ?>> Exchange </option>

        </select>

        <!-- CATEGORY -->

        <select name="category">

            <option value="">
                All Categories
            </option>

            <option value="Programming" <?php echo ($category === "Programming") ? "selected" : ""; ?>> Programming
            </option>

            <option value="Business" <?php echo ($category === "Business") ? "selected" : ""; ?>> Business </option>

            <option value="ACCA" <?php echo ($category === "ACCA") ? "selected" : ""; ?>> ACCA </option>

            <option value="Engineering" <?php echo ($category === "Engineering") ? "selected" : ""; ?>> Engineering
            </option>

            <option value="Medical" <?php echo ($category === "Medical") ? "selected" : ""; ?>> Medical </option>

            <option value="Novel" <?php echo ($category === "Novel") ? "selected" : ""; ?>> Novel </option>

            <option value="Reference" <?php echo ($category === "Reference") ? "selected" : ""; ?>> Reference </option>

            <option value="Other" <?php echo ($category === "Other") ? "selected" : ""; ?>> Other </option>

        </select>

        <!-- CONDITION -->

        <select name="condition">

            <option value="">
                All Conditions
            </option>

            <option value="New" <?php echo ($condition === "New") ? "selected" : ""; ?>> New </option>

            <option value="Like New" <?php echo ($condition === "Like New") ? "selected" : ""; ?>> Like New </option>

            <option value="Good" <?php echo ($condition === "Good") ? "selected" : ""; ?>> Good </option>

            <option value="Fair" <?php echo ($condition === "Fair") ? "selected" : ""; ?>> Fair </option>

            <option value="Poor" <?php echo ($condition === "Poor") ? "selected" : ""; ?>> Poor </option>

        </select>

        <button type="submit">
            Apply Filters
        </button>

        <a href="browsebook.php" class="clear-filters">
            Clear
        </a>

    </form>

    <!-- =====================================
         RESULTS
    ====================================== -->

    <div class="books-grid">

        <?php

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

                        <?php if ($row['type'] === "Sell") { ?>

                            <h4>

                                Rs.
                                <?php echo number_format(
                                    (float) $row['price'],
                                    2
                                ); ?>

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

                        <a href="bookdetails.php?id=<?php echo (int) $row['id']; ?>" class="view-book-btn">
                            View Details
                        </a>

                    </div>

                </div>

                <?php

            }

        } else {

            ?>

            <div class="no-books">

                <h3>
                    No books found
                </h3>

                <p>
                    Try changing your search or filters.
                </p>

                <a href="browsebook.php" class="btn-primary">
                    View All Books
                </a>

            </div>

            <?php

        }

        ?>

    </div>

</section>

<?php

include("footer.php");

?>