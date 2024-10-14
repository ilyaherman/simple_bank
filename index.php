<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Bank</title>
    <link rel="stylesheet" href="frontend/css/styles.css">
</head>
<body>

    <div id="main_block_page">
        <div class="page_block_m">
            <h1>Welcome to Simple Bank</h1>

             <?php if (isset($_SESSION['user_id'])): ?>
                <p>Logged in as User ID: <?= $_SESSION['user_id']; ?></p>
                <a href="frontend/dashboard.php">Go to Dashboard</a>
                <a href="frontend/logout.php">Logout</a>
                <br><br>
                <form action="../logout.php" method="POST">
                    <button type="submit">Logout</button>
                </form>
            <?php else: ?>
                <p><a href="frontend/register.php">Register</a> or <a href="frontend/login.php">Login</a> to start using Simple Bank</p>
            <?php endif; ?>
            
        </div>
    </div>

</body>
</html>
