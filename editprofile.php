<?php

session_start();
include("conn.php");

// LOGIN CHECK

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$error = "";
$success = "";

// FETCH CURRENT USER

$stmt = $conn->prepare("
    SELECT
        fullname,
        username,
        email,
        profile_image
    FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    session_destroy();

    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();

$stmt->close();

// UPDATE PROFILE

if (isset($_POST['update_profile'])) {

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);

    // ======================================
    // VALIDATION
    // ======================================

    if ($fullname === "" || $email === "") {

        $error = "Full name and email are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";
    }

    // ======================================
    // CHECK EMAIL
    // ======================================

    if ($error === "") {

        $checkStmt = $conn->prepare("
            SELECT id
            FROM users
            WHERE email = ?
            AND id != ?
        ");

        $checkStmt->bind_param(
            "si",
            $email,
            $user_id
        );

        $checkStmt->execute();

        $emailResult = $checkStmt->get_result();

        if ($emailResult->num_rows > 0) {

            $error = "That email address is already being used.";

        }

        $checkStmt->close();
    }

    // ======================================
    // PROFILE IMAGE
    // ======================================

    $newProfileImage = $user['profile_image'];

    if (
        $error === "" &&
        isset($_FILES['profile_image']) &&
        $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {

            $error = "There was a problem uploading the image.";

        } else {

            $allowed = [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ];

            $originalName =
                $_FILES['profile_image']['name'];

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

                // Make sure uploads directory exists
                if (!is_dir("uploads")) {
                    mkdir("uploads", 0755, true);
                }

                $newProfileImage =
                    uniqid("profile_", true) .
                    "." .
                    $extension;

                $uploadPath =
                    "uploads/" . $newProfileImage;

                if (
                    !move_uploaded_file(
                        $_FILES['profile_image']['tmp_name'],
                        $uploadPath
                    )
                ) {

                    $error =
                        "Failed to upload the profile image.";

                    $newProfileImage =
                        $user['profile_image'];
                }
            }
        }
    }

    // ======================================
    // UPDATE DATABASE
    // ======================================

    if ($error === "") {

        $updateStmt = $conn->prepare("
            UPDATE users
            SET
                fullname = ?,
                email = ?,
                profile_image = ?
            WHERE id = ?
        ");

        $updateStmt->bind_param(
            "sssi",
            $fullname,
            $email,
            $newProfileImage,
            $user_id
        );

        if ($updateStmt->execute()) {

            $updateStmt->close();

            // ==================================
            // DELETE OLD PROFILE IMAGE
            // ==================================

            if (
                $newProfileImage !== $user['profile_image'] &&
                !empty($user['profile_image'])
            ) {

                $oldImagePath =
                    "uploads/" .
                    $user['profile_image'];

                if (file_exists($oldImagePath)) {

                    unlink($oldImagePath);
                }
            }

            // Update session information
            $_SESSION['fullname'] = $fullname;

            header("Location: profile.php");
            exit();

        } else {

            $error =
                "Failed to update your profile.";

            $updateStmt->close();

            // Delete newly uploaded image
            // if database update failed

            if (
                $newProfileImage !== $user['profile_image'] &&
                file_exists(
                    "uploads/" . $newProfileImage
                )
            ) {

                unlink(
                    "uploads/" . $newProfileImage
                );
            }
        }
    }
}

// HEADER

include("header.php");

?>
<link rel="stylesheet" href="css/edit-profile.css">

<section class="edit-profile-section">

    <div class="edit-profile-card">

        <div class="edit-profile-header">

            <h1>Edit Profile</h1>

            <p>
                Update your Booked UP profile
            </p>

        </div>

        <!-- =================================
             ERROR
        ================================== -->

        <?php if ($error !== "") { ?>

            <div class="profile-error">

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>

        <?php } ?>

        <!-- =================================
             FORM
        ================================== -->

        <form method="POST" enctype="multipart/form-data">

            <!-- PROFILE IMAGE -->

            <div class="profile-image-preview">

                <?php if (!empty($user['profile_image'])) { ?>

                    <img src="uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" id="profilePreview"
                        alt="Profile Image">

                <?php } else { ?>

                    <div class="profile-default-preview" id="defaultPreview">

                        <i class="fa-solid fa-user"></i>

                    </div>

                    <img src="" id="profilePreview" alt="Profile Image" style="display:none;">

                <?php } ?>

            </div>

            <label>
                Profile Picture
            </label>

            <input type="file" name="profile_image" id="profileImage" accept=".jpg,.jpeg,.png,.webp">

            <small class="profile-image-help">
                JPG, JPEG, PNG or WEBP
            </small>

            <!-- FULL NAME -->

            <label>
                Full Name
            </label>

            <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>

            <!-- USERNAME -->

            <label>
                Username
            </label>

            <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>

            <small class="username-help">
                Username cannot be changed.
            </small>

            <!-- EMAIL -->

            <label>
                Email
            </label>

            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <!-- BUTTONS -->

            <div class="edit-profile-actions">

                <a href="profile.php" class="cancel-profile-btn">
                    Cancel
                </a>

                <button type="submit" name="update_profile" class="save-profile-btn">
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</section>

<script>

    // PROFILE IMAGE PREVIEW

    const profileImage =
        document.getElementById("profileImage");

    const profilePreview =
        document.getElementById("profilePreview");

    const defaultPreview =
        document.getElementById("defaultPreview");

    profileImage.addEventListener(
        "change",
        function () {

            const file = this.files[0];

            if (!file) {
                return;
            }

            profilePreview.src =
                URL.createObjectURL(file);

            profilePreview.style.display =
                "block";

            if (defaultPreview) {
                defaultPreview.style.display =
                    "none";
            }

        }
    );

</script>

<?php

include("footer.php");

?>