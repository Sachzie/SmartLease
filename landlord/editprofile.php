<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Basic Reset */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4e1c5;
            color: #5a3e2b;
            padding-top: 80px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            max-width: 600px;
            width: 100%;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            margin-bottom: 15px;
            color: #5a3e2b;
        }

        p {
            margin-bottom: 15px;
            color: green;
        }

        .form-group {
            text-align: left;
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: #5a3e2b;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #8b5a2b;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #8b5a2b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        button:hover {
            background: #6e4123;
        }

        .profile-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #8b5a2b;
            margin-bottom: 10px;
        }

        .upload-btn {
            padding: 10px;
            border: 1px solid #8b5a2b;
            background: #f4e1c5;
            cursor: pointer;
            border-radius: 5px;
            color: #5a3e2b;
            font-weight: bold;
        }

        .upload-btn:hover {
            background: #e6c8a0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Profile</h2>
        <?php if (!empty($message)) echo "<p>$message</p>"; ?>

        <div class="profile-section">
            <label>Current Profile Picture:</label>
            <?php if (!empty($tenant['picture'])): ?>
                <img src="<?php echo $tenant['picture']; ?>" class="profile-pic" id="profilePreview">
            <?php else: ?>
                <img src="default-avatar.png" class="profile-pic" id="profilePreview">
            <?php endif; ?>
        </div>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Date of Birth:</label>
                <input type="date" name="date_of_birth" value="<?php echo $tenant['date_of_birth']; ?>" required>
            </div>

            <div class="form-group">
                <label>Sex:</label>
                <select name="sex">
                    <option value="Male" <?php if ($tenant['sex'] == 'Male') echo "selected"; ?>>Male</option>
                    <option value="Female" <?php if ($tenant['sex'] == 'Female') echo "selected"; ?>>Female</option>
                    <option value="Other" <?php if ($tenant['sex'] == 'Other') echo "selected"; ?>>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label>Address:</label>
                <textarea name="address"><?php echo $tenant['address']; ?></textarea>
            </div>

            <div class="form-group">
                <label>Phone:</label>
                <input type="text" name="phone" value="<?php echo $tenant['phone']; ?>">
            </div>

            <div class="form-group">
                <label>Upload New Profile Picture:</label>
                <input type="file" name="picture" accept="image/*" id="uploadProfile">
            </div>

            <button type="submit">Update Profile</button>
        </form>
    </div>

    <script>
        // Live Preview for New Profile Picture
        document.getElementById('uploadProfile').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePreview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>