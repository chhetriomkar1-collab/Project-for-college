<?php
session_start();

if(!isset($_SESSION['id'])){
    header("Location: login.php");
    exit();
}
include("conn.php");
include("header.php");
?>
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
            <select name="category" required>
                <option value="">Select Category</option>

                <option>Programming</option>
                <option>Business</option>
                <option>ACCA</option>
                <option>Engineering</option>
                <option>Medical</option>
                <option>Novel</option>
                <option>Reference</option>
            </select>
            <label>Condition</label>
            <select name="condition">
                <option>New</option>
                <option>Like New</option>
                <option>Good</option>
                <option>Fair</option>
                <option>Poor</option>
            </select>
            <label>Listing Type</label>
            <select name="type">
              <option>Sell</option>
                <option>Exchange</option>
            </select>
            <label>Price (Rs.)</label>
            <input type="number" name="price">
            <label>Description</label>
            <textarea
                name="description"
                rows="5"
                placeholder="Describe the book..."></textarea>
            <button type="submit" name="submit">
                Add Book
            </button>
        </form>
    </div>
</div>
<?php
include("footer.php");
?>