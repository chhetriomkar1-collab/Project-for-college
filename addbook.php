<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}
include("conn.php");
include("header.php");
?>
<link rel="stylesheet" href="css/forms.css">
<div class="add-book-container">
    <div class="add-book-card">
        <h1>Add New Book</h1>
        <p>List your book for sale or exchange.</p>
        <form action="savebook.php" method="POST" enctype="multipart/form-data">
            <label>Book Cover</label>
            <input type="file" name="image" required>
            <label>Book Title</label>
            <input type="text" name="title" required>
            <label>Author</label>
            <input type="text" name="author" required>
            <label>Category</label>

            <select name="category" id="category" required>
                <option value="">Select Category</option>

                <option value="Programming">Programming</option>
                <option value="Business">Business</option>
                <option value="ACCA">ACCA</option>
                <option value="Engineering">Engineering</option>
                <option value="Medical">Medical</option>
                <option value="Novel">Novel</option>
                <option value="Reference">Reference</option>
                <option value="Other">Other</option>
            </select>

            <input type="text" name="other_category" id="otherCategory" placeholder="Enter your category"
                style="display: none;">
            <label>Condition</label>
            <select name="condition">
                <option>New</option>
                <option>Like New</option>
                <option>Good</option>
                <option>Fair</option>
                <option>Poor</option>
            </select>
            <label>Listing Type</label>

            <select name="type" id="listingType" required>
                <option value="Sell">Sell</option>
                <option value="Exchange">Exchange</option>
            </select>

            <div id="priceField">

                <label>Price (Rs.)</label>

                <input type="number" name="price" id="price" min="0" step="0.01" placeholder="Enter price">

            </div>
            <label>Description</label>
            <textarea name="description" rows="5" placeholder="Describe the book..."></textarea>
            <button type="submit" name="submit">
                Add Book
            </button>
        </form>
    </div>
</div>
<script>

    // OTHER CATEGORY

    const categorySelect =
        document.getElementById("category");

    const otherCategory =
        document.getElementById("otherCategory");

    categorySelect.addEventListener("change", function () {

        if (this.value === "Other") {

            otherCategory.style.display = "block";

            otherCategory.required = true;

        } else {

            otherCategory.style.display = "none";

            otherCategory.required = false;

            otherCategory.value = "";

        }

    });

    // LISTING TYPE / PRICE

    const listingType =
        document.getElementById("listingType");

    const priceField =
        document.getElementById("priceField");

    const priceInput =
        document.getElementById("price");

    function updatePriceField() {

        if (listingType.value === "Sell") {

            // Show price
            priceField.style.display = "block";
            priceInput.required = true;

        } else {
            priceField.style.display = "none";
            priceInput.required = false;
            priceInput.value = "";

        }

    }

    updatePriceField();

    listingType.addEventListener(
        "change",
        updatePriceField
    );

</script>
<?php
include("footer.php");
?>