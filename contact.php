<?php

session_start();
include("conn.php");

$message = "";
$messageType = "";

if (isset($_POST['send_message'])) {

    $name = trim($_POST['name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $subject = trim($_POST['subject'] ?? "");
    $userMessage = trim($_POST['message'] ?? "");

    if (
        $name === "" ||
        $email === "" ||
        $subject === "" ||
        $userMessage === ""
    ) {

        $message = "Please fill in all fields.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO contact_messages
            (name, email, subject, message)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssss",
            $name,
            $email,
            $subject,
            $userMessage
        );

        if ($stmt->execute()) {

            $message = "Your message has been sent successfully!";
            $messageType = "success";

        } else {

            $message = "Something went wrong. Please try again.";
            $messageType = "error";
        }

        $stmt->close();
    }
}

include("header.php");

?>

<link rel="stylesheet" href="css/contact.css">

<section class="contact-section">

    <div class="contact-container">

        <div class="contact-header">

            <span class="contact-badge">
                <i class="fa-solid fa-envelope"></i>
                Get In Touch
            </span>

            <h1>Contact Booked UP</h1>

            <p>
                Have a question, suggestion, or problem?
                Send us a message and we'll get back to you.
            </p>

        </div>

        <div class="contact-card">

            <?php if ($message !== "") { ?>

                <div class="<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php } ?>

            <form method="POST">

                <div class="form-group">

                    <label for="name">
                        Name
                    </label>

                    <input type="text" id="name" name="name" placeholder="Enter your name" value="<?php
                    echo htmlspecialchars(
                        $_POST['name'] ?? ''
                    );
                    ?>" required>

                </div>

                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php
                    echo htmlspecialchars(
                        $_POST['email'] ?? ''
                    );
                    ?>" required>

                </div>

                <div class="form-group">

                    <label for="subject">
                        Subject
                    </label>

                    <input type="text" id="subject" name="subject" placeholder="What is this about?" value="<?php
                    echo htmlspecialchars(
                        $_POST['subject'] ?? ''
                    );
                    ?>" required>

                </div>

                <div class="form-group">

                    <label for="message">
                        Message
                    </label>

                    <textarea id="message" name="message" rows="6" placeholder="Write your message..." required><?php
                    echo htmlspecialchars(
                        $_POST['message'] ?? ''
                    );
                    ?></textarea>

                </div>

                <button type="submit" name="send_message" class="contact-submit">

                    <i class="fa-solid fa-paper-plane"></i>

                    Send Message

                </button>

            </form>

        </div>

    </div>

</section>