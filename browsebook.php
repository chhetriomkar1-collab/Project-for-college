<?php
session_start();
include("conn.php");
include("header.php");
?>
<section class="books-section">

    <div class="section-title">

        <h2>Latest Books</h2>

        <p>Recently added books from students</p>

    </div>

    <div class="books-grid">

        <?php

        $sql = "SELECT books.*, users.username
                FROM books
                JOIN users
                ON books.user_id = users.id
                ORDER BY books.created_at DESC";

        $result = mysqli_query($conn,$sql);

        while($row = mysqli_fetch_assoc($result))
        {
        ?>

            <div class="book-card">

                <img
                src="images/<?php echo $row['image']; ?>">

                <div class="book-info">

                    <h3><?php echo $row['title']; ?></h3>

                    <p>
                        <strong>Author:</strong>
                        <?php echo $row['author']; ?>
                    </p>

                    <p>
                        <strong>Category:</strong>
                        <?php echo $row['category']; ?>
                    </p>

                    <p>
                        <strong>Condition:</strong>
                        <?php echo $row['book_condition']; ?>
                    </p>

                    <p>
                        <strong>Type:</strong>
                        <?php echo $row['type']; ?>
                    </p>

                    <?php
                    if($row['type']=="Sell")
                    {
                    ?>
                        <h4>
                            Rs. <?php echo number_format($row['price']); ?>
                        </h4>
                    <?php
                    }
                    else
                    {
                    ?>
                        <h4 style="color:green;">
                            Available for Exchange
                        </h4>
                    <?php
                    }
                    ?>

                    <small>
                        Posted by
                        <?php echo htmlspecialchars($row['username']); ?>
                    </small>

                </div>

            </div>

        <?php
        }

        ?>

    </div>

</section>