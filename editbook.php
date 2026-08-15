<?php

session_start();
include("conn.php");

// ==========================================
// LOGIN CHECK
// ==========================================

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


// ==========================================
// CHECK BOOK ID
// ==========================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: profile.php");
    exit();
}

$book_id = (int) $_GET['id'];


// ==========================================
// FETCH BOOK
// IMPORTANT: user_id ensures ownership
// ==========================================

$stmt = $conn->prepare("
    SELECT *
    FROM books
    WHERE id = ?
    AND user_id = ?
");

$stmt->bind_param("ii", $book_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: profile.php");
    exit();
}

$book = $result->fetch_assoc();

$stmt->close();


// ==========================================
// UPDATE BOOK
// ==========================================

$error = "";

if (isset($_POST['update_book'])) {

    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $category = trim($_POST['category']);
    $other_category = trim($_POST['other_category'] ?? "");
    $condition = trim($_POST['condition']);
    $type = trim($_POST['type']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);


    // ======================================
    // OTHER CATEGORY
    // ======================================

    if ($category === "Other") {

        if ($other_category === "") {

            $error = "Please enter your category.";

        } else {

            $category = $other_category;
        }
    }


    // ======================================
    // BASIC VALIDATION
    // ======================================

    if ($error === "") {

        if (
            $title === "" ||
            $author === "" ||
            $category === ""
        ) {

            $error = "Please fill in all required fields.";

        }

        elseif (
            $condition === "" ||
            !in_array(
                $condition,
                ["New", "Like New", "Good", "Fair", "Poor"]
            )
        ) {

            $error = "Invalid book condition.";

        }

        elseif (
            $type !== "Sell" &&
            $type !== "Exchange"
        ) {

            $error = "Invalid listing type.";

        }

        // Sell must have a price
        elseif ($type === "Sell" && $price === "") {

            $error = "Please enter a price for a book you want to sell.";

        }

        // Price must be numeric
        elseif (
            $type === "Sell" &&
            (!is_numeric($price) || $price < 0)
        ) {

            $error = "Please enter a valid price.";

        }
    }


    // ======================================
    // IMAGE
    // ======================================

    $newImage = $book['image'];

    if (
        $error === "" &&
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

            $error = "There was a problem uploading the image.";

        } else {

            $allowed = [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ];

            $originalName = $_FILES['image']['name'];

            $extension = strtolower(
                pathinfo(
                    $originalName,
                    PATHINFO_EXTENSION
                )
            );


            if (!in_array($extension, $allowed)) {

                $error =
                    "Only JPG, JPEG, PNG and WEBP files are allowed.";

            } else {

                $newImage =
                    uniqid("book_", true) .
                    "." .
                    $extension;

                $uploadPath =
                    "images/" . $newImage;


                if (!move_uploaded_file(
                    $_FILES['image']['tmp_name'],
                    $uploadPath
                )) {

                    $error =
                        "Failed to upload the new image.";

                    $newImage = $book['image'];
                }
            }
        }
    }


    // ======================================
    // UPDATE DATABASE
    // ======================================

    if ($error === "") {

        // Exchange listings don't need a price
        if ($type === "Exchange") {

            $price = null;
        }


        $updateStmt = $conn->prepare("
            UPDATE books
            SET
                title = ?,
                author = ?,
                category = ?,
                book_condition = ?,
                type = ?,
                price = ?,
                description = ?,
                image = ?
            WHERE id = ?
            AND user_id = ?
        ");


        $updateStmt->bind_param(
            "ssssssssii",
            $title,
            $author,
            $category,
            $condition,
            $type,
            $price,
            $description,
            $newImage,
            $book_id,
            $user_id
        );


        if ($updateStmt->execute()) {

            $updateStmt->close();


            // ==================================
            // DELETE OLD IMAGE
            // ==================================

            if (
                $newImage !== $book['image'] &&
                !empty($book['image'])
            ) {

                $oldImagePath =
                    "images/" . $book['image'];

                if (file_exists($oldImagePath)) {

                    unlink($oldImagePath);
                }
            }


            header(
                "Location: profile.php"
            );

            exit();

        } else {

            $error =
                "Failed to update the book.";

            $updateStmt->close();

            // If new image was uploaded but DB update failed,
            // remove the unused new image.
            if (
                $newImage !== $book['image'] &&
                file_exists("images/" . $newImage)
            ) {

                unlink("images/" . $newImage);
            }
        }
    }
}


// ==========================================
// HEADER
// ==========================================

include("header.php");

?>
<link rel="stylesheet" href="css/edit-book.css">

<section class="edit-book-section">

    <div class="edit-book-card">

        <div class="edit-book-header">

            <h1>Edit Book</h1>

            <p>
                Update your book listing
            </p>

        </div>


        <?php if ($error !== "") { ?>

            <div class="edit-book-error">

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php } ?>


        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <!-- =================================
                 CURRENT IMAGE
            ================================== -->

            <div class="current-book-image">

                <img
                    src="images/<?php echo htmlspecialchars($book['image']); ?>"
                    alt="Current Book Cover"
                    id="imagePreview"
                >

            </div>


            <!-- =================================
                 NEW IMAGE
            ================================== -->

            <label>
                Book Cover
            </label>

            <input
                type="file"
                name="image"
                accept=".jpg,.jpeg,.png,.webp"
                id="bookImage"
            >

            <small class="image-help">
                Leave empty to keep the current image.
            </small>


            <!-- =================================
                 TITLE
            ================================== -->

            <label>
                Book Title
            </label>

            <input
                type="text"
                name="title"
                value="<?php echo htmlspecialchars($book['title']); ?>"
                required
            >


            <!-- =================================
                 AUTHOR
            ================================== -->

            <label>
                Author
            </label>

            <input
                type="text"
                name="author"
                value="<?php echo htmlspecialchars($book['author']); ?>"
                required
            >


            <!-- =================================
                 CATEGORY
            ================================== -->

            <label>
                Category
            </label>

            <?php

            $defaultCategories = [
                "Programming",
                "Business",
                "ACCA",
                "Engineering",
                "Medical",
                "Novel",
                "Reference"
            ];

            $isDefaultCategory =
                in_array(
                    $book['category'],
                    $defaultCategories
                );

            ?>

            <select
                name="category"
                id="category"
                required
            >

                <option value="">
                    Select Category
                </option>

                <?php foreach (
                    $defaultCategories
                    as $cat
                ) { ?>

                    <option
                        value="<?php echo htmlspecialchars($cat); ?>"
                        <?php
                        echo (
                            $book['category'] === $cat
                        )
                            ? "selected"
                            : "";
                        ?>
                    >
                        <?php echo htmlspecialchars($cat); ?>
                    </option>

                <?php } ?>

                <option
                    value="Other"
                    <?php
                    echo !$isDefaultCategory
                        ? "selected"
                        : "";
                    ?>
                >
                    Other
                </option>

            </select>


            <!-- =================================
                 OTHER CATEGORY
            ================================== -->

            <input
                type="text"
                name="other_category"
                id="otherCategory"
                placeholder="Enter your category"
                value="<?php
                    echo !$isDefaultCategory
                        ? htmlspecialchars($book['category'])
                        : "";
                ?>"
                style="<?php
                    echo !$isDefaultCategory
                        ? "display:block;"
                        : "display:none;";
                ?>"
            >


            <!-- =================================
                 CONDITION
            ================================== -->

            <label>
                Condition
            </label>

            <select
                name="condition"
                required
            >

                <?php

                $conditions = [
                    "New",
                    "Like New",
                    "Good",
                    "Fair",
                    "Poor"
                ];

                foreach ($conditions as $conditionOption) {

                ?>

                    <option
                        value="<?php
                            echo htmlspecialchars(
                                $conditionOption
                            );
                        ?>"
                        <?php
                        echo (
                            $book['book_condition']
                            === $conditionOption
                        )
                            ? "selected"
                            : "";
                        ?>
                    >

                        <?php
                        echo htmlspecialchars(
                            $conditionOption
                        );
                        ?>

                    </option>

                <?php } ?>

            </select>


            <!-- =================================
                 LISTING TYPE
            ================================== -->

            <label>
                Listing Type
            </label>

            <select
                name="type"
                id="listingType"
                required
            >

                <option
                    value="Sell"
                    <?php
                    echo (
                        $book['type'] === "Sell"
                    )
                        ? "selected"
                        : "";
                    ?>
                >
                    Sell
                </option>

                <option
                    value="Exchange"
                    <?php
                    echo (
                        $book['type'] === "Exchange"
                    )
                        ? "selected"
                        : "";
                    ?>
                >
                    Exchange
                </option>

            </select>


            <!-- =================================
                 PRICE
            ================================== -->

            <div id="priceContainer">

                <label>
                    Price (Rs.)
                </label>

                <input
                    type="number"
                    name="price"
                    id="price"
                    min="0"
                    step="0.01"
                    value="<?php
                        echo $book['price'] !== null
                            ? htmlspecialchars($book['price'])
                            : "";
                    ?>"
                >

            </div>


            <!-- =================================
                 DESCRIPTION
            ================================== -->

            <label>
                Description
            </label>

            <textarea
                name="description"
                rows="6"
                placeholder="Describe the book..."
            ><?php echo htmlspecialchars($book['description'] ?? ""); ?></textarea>


            <!-- =================================
                 BUTTONS
            ================================== -->

            <div class="edit-book-actions">

                <a
                    href="profile.php"
                    class="cancel-edit-btn"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    name="update_book"
                    class="update-book-btn"
                >
                    Update Book
                </button>

            </div>

        </form>

    </div>

</section>


<script>

// ==========================================
// OTHER CATEGORY
// ==========================================

const categorySelect =
    document.getElementById("category");

const otherCategory =
    document.getElementById("otherCategory");


function handleCategory() {

    if (categorySelect.value === "Other") {

        otherCategory.style.display = "block";

        otherCategory.required = true;

    } else {

        otherCategory.style.display = "none";

        otherCategory.required = false;

        otherCategory.value = "";
    }
}


categorySelect.addEventListener(
    "change",
    handleCategory
);


// ==========================================
// SELL / EXCHANGE PRICE
// ==========================================

const listingType =
    document.getElementById("listingType");

const priceContainer =
    document.getElementById("priceContainer");

const priceInput =
    document.getElementById("price");


function handleListingType() {

    if (listingType.value === "Sell") {

        priceContainer.style.display = "block";

        priceInput.required = true;

    } else {

        priceContainer.style.display = "none";

        priceInput.required = false;

        priceInput.value = "";
    }
}


listingType.addEventListener(
    "change",
    handleListingType
);


// ==========================================
// IMAGE PREVIEW
// ==========================================

const imageInput =
    document.getElementById("bookImage");

const imagePreview =
    document.getElementById("imagePreview");


imageInput.addEventListener(
    "change",
    function () {

        const file = this.files[0];

        if (file) {

            imagePreview.src =
                URL.createObjectURL(file);
        }

    }
);


// Set initial state
handleCategory();
handleListingType();

</script>


<?php

include("footer.php");

?>