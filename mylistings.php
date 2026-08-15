<?php

session_start();
include("conn.php");

// LOGIN CHECK

$user_id = $_SESSION['user_id']
    ?? $_SESSION['id']
    ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $user_id;

// DELETE BOOK

if (isset($_POST['delete_book'])) {

    $book_id = (int) $_POST['book_id'];

    // Get image first

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

    $imageName = "";

    if ($imageResult->num_rows === 1) {

        $imageRow = $imageResult->fetch_assoc();

        $imageName = $imageRow['image'];
    }

    $imageStmt->close();

    // Delete only if it belongs to current user

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

    // Delete image

    if (!empty($imageName)) {

        $imagePath =
            "images/" . $imageName;

        if (file_exists($imagePath)) {

            unlink($imagePath);
        }
    }

    header("Location: mylistings.php");

    exit();
}

// FILTERS

$search = trim($_GET['search'] ?? "");

$type = trim($_GET['type'] ?? "");

$condition = trim($_GET['condition'] ?? "");

// BASE QUERY

$sql = "
    SELECT *
    FROM books
    WHERE user_id = ?
";

// SEARCH

if ($search !== "") {

    $sql .= "
        AND (
            title LIKE ?
            OR author LIKE ?
            OR category LIKE ?
        )
    ";
}

// TYPE

if ($type === "Sell" || $type === "Exchange") {

    $sql .= "
        AND type = ?
    ";
}

// CONDITION

$allowedConditions = [
    "New",
    "Like New",
    "Good",
    "Fair",
    "Poor"
];

if (in_array($condition, $allowedConditions)) {

    $sql .= "
        AND book_condition = ?
    ";
}

// ORDER

$sql .= "
    ORDER BY created_at DESC
";

// PREPARE

$stmt = $conn->prepare($sql);

// BIND PARAMETERS

$types = "i";

$params = [$user_id];

if ($search !== "") {

    $searchValue = "%" . $search . "%";

    $types .= "sss";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
}

if ($type === "Sell" || $type === "Exchange") {

    $types .= "s";

    $params[] = $type;
}

if (in_array($condition, $allowedConditions)) {

    $types .= "s";

    $params[] = $condition;
}

// Dynamic bind_param

$bindValues = [];

$bindValues[] = $types;

foreach ($params as $key => $value) {
    $bindValues[] = &$params[$key];
}

call_user_func_array(
    [$stmt, 'bind_param'],
    $bindValues
);

$stmt->execute();

$books = $stmt->get_result();

// USER INFORMATION

$userStmt = $conn->prepare("
    SELECT
        fullname,
        username,
        profile_image
    FROM users
    WHERE id = ?
");

$userStmt->bind_param(
    "i",
    $user_id
);

$userStmt->execute();

$user = $userStmt
    ->get_result()
    ->fetch_assoc();

$userStmt->close();

include("header.php");

?>
<link rel="stylesheet" href="css/listings.css">

<section class="my-listings-section">

    <div class="my-listings-container">

        <!-- =================================
             HEADER
        ================================== -->

        <div class="my-listings-header">

            <div>

                <span class="dashboard-badge user-badge">

                    <i class="fa-solid fa-book"></i>

                    My Marketplace

                </span>

                <h1>
                    My Listings
                </h1>

                <p>
                    Manage the books you have listed on Booked UP.
                </p>

            </div>

            <a href="addbook.php" class="add-listing-btn">

                <i class="fa-solid fa-plus"></i>

                Add Book

            </a>

        </div>

        <!-- =================================
             FILTERS
        ================================== -->

        <form method="GET" class="my-listing-filters">

            <div class="my-listing-search">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input type="text" name="search" placeholder="Search your listings..."
                    value="<?php echo htmlspecialchars($search); ?>">

            </div>

            <select name="type">

                <option value="">
                    All Types
                </option>

                <option value="Sell" <?php
                echo $type === "Sell"
                    ? "selected"
                    : "";
                ?>>
                    Sell
                </option>

                <option value="Exchange" <?php
                echo $type === "Exchange"
                    ? "selected"
                    : "";
                ?>>
                    Exchange
                </option>

            </select>

            <select name="condition">

                <option value="">
                    All Conditions
                </option>

                <?php foreach ($allowedConditions as $option) { ?>

                    <option value="<?php echo htmlspecialchars($option); ?>" <?php
                       echo $condition === $option
                           ? "selected"
                           : "";
                       ?>>
                        <?php echo htmlspecialchars($option); ?>
                    </option>

                <?php } ?>

            </select>

            <button type="submit">

                Apply

            </button>

            <a href="mylistings.php" class="clear-listing-filter">
                Clear
            </a>

        </form>

        <!-- =================================
             RESULTS
        ================================== -->

        <?php if ($books->num_rows > 0) { ?>

            <div class="my-listings-grid">

                <?php while ($book = $books->fetch_assoc()) { ?>

                    <div class="my-listing-card">

                        <!-- IMAGE -->

                        <img src="images/<?php echo htmlspecialchars($book['image']); ?>"
                            alt="<?php echo htmlspecialchars($book['title']); ?>">

                        <div class="my-listing-content">

                            <div class="my-listing-top">

                                <span class="
                                    my-listing-type
                                    <?php
                                    echo $book['type'] === "Sell"
                                        ? "my-sell"
                                        : "my-exchange";
                                    ?>
                                ">

                                    <?php
                                    echo htmlspecialchars(
                                        $book['type']
                                    );
                                    ?>

                                </span>

                            </div>

                            <h2>

                                <?php
                                echo htmlspecialchars(
                                    $book['title']
                                );
                                ?>

                            </h2>

                            <p class="listing-author">

                                By
                                <?php
                                echo htmlspecialchars(
                                    $book['author']
                                );
                                ?>

                            </p>

                            <div class="listing-meta">

                                <span>

                                    <strong>
                                        Category:
                                    </strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $book['category']
                                    );
                                    ?>

                                </span>

                                <span>

                                    <strong>
                                        Condition:
                                    </strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $book['book_condition']
                                    );
                                    ?>

                                </span>

                            </div>

                            <?php if ($book['type'] === "Sell") { ?>

                                <div class="my-listing-price">

                                    Rs.
                                    <?php
                                    echo number_format(
                                        (float) $book['price'],
                                        2
                                    );
                                    ?>

                                </div>

                            <?php } else { ?>

                                <div class="my-listing-price exchange-price-text">

                                    Available for Exchange

                                </div>

                            <?php } ?>

                            <small class="listing-date">

                                Listed
                                <?php
                                echo date(
                                    "M d, Y",
                                    strtotime(
                                        $book['created_at']
                                    )
                                );
                                ?>

                            </small>

                            <!-- ACTIONS -->

                            <div class="my-listing-actions">

                                <a href="bookdetails.php?id=<?php echo (int) $book['id']; ?>" class="listing-view-action">
                                    <i class="fa-solid fa-eye"></i>
                                    View
                                </a>

                                <a href="editbook.php?id=<?php echo (int) $book['id']; ?>" class="listing-edit-action">
                                    <i class="fa-solid fa-pen"></i>
                                    Edit
                                </a>

                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this listing?');">

                                    <input type="hidden" name="book_id" value="<?php echo (int) $book['id']; ?>">

                                    <button type="submit" name="delete_book" class="listing-delete-action">
                                        <i class="fa-solid fa-trash"></i>
                                        Delete
                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                <?php } ?>

            </div>

        <?php } else { ?>

            <!-- EMPTY -->

            <div class="my-listings-empty">

                <i class="fa-solid fa-book-open"></i>

                <h2>
                    No listings found
                </h2>

                <?php if ($search !== "" || $type !== "" || $condition !== "") { ?>

                    <p>
                        Try changing your search or filters.
                    </p>

                    <a href="mylistings.php" class="btn-primary">
                        Show All Listings
                    </a>

                <?php } else { ?>

                    <p>
                        You haven't listed any books yet.
                    </p>

                    <a href="addbook.php" class="btn-primary">
                        List Your First Book
                    </a>

                <?php } ?>

            </div>

        <?php } ?>

    </div>

</section>

<?php

$stmt->close();

include("footer.php");

?>