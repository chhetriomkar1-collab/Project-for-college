<?php
session_start();
include("conn.php");

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['submit'])) {

    $user_id = $_SESSION['id'];

    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $category = trim($_POST['category']);

    if ($category === "Other") {

        $other_category =
            trim($_POST['other_category'] ?? "");

        if ($other_category === "") {

            die("Please enter your category.");

        }

        $category = $other_category;
    }
    $condition = trim($_POST['condition']);
    $type = trim($_POST['type']);
    $price = trim($_POST['price']);
    if ($type === "Exchange") {

        $price = "";

    } elseif ($type === "Sell") {

        if ($price === "") {

            die("Please enter a price for a sale listing.");

        }

    }
    $description = trim($_POST['description']);

    $imageName = "";

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        $image = $_FILES['image']['name'];

        $extension = strtolower(pathinfo($image, PATHINFO_EXTENSION));

        if (in_array($extension, $allowed)) {

            $imageName = uniqid() . "." . $extension;

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                "images/" . $imageName
            );
        } else {
            die("Only JPG, JPEG, PNG and WEBP files are allowed.");
        }
    }

    $stmt = $conn->prepare("INSERT INTO books
    (user_id,title,author,category,book_condition,type,price,description,image)
    VALUES (?,?,?,?,?,?,?,?,?)");

    $stmt->bind_param(
        "issssssss",
        $user_id,
        $title,
        $author,
        $category,
        $condition,
        $type,
        $price,
        $description,
        $imageName
    );

    if ($stmt->execute()) {

        header("Location: indexp.php");
        exit();

    } else {

        echo "Error : " . $stmt->error;

    }

    $stmt->close();

}
?>