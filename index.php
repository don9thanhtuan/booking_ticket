
<?php
session_start(); // Bắt đầu session

// if (!isset($_SESSION['user_id'])) {
//     header("Location: auth/login.php"); // Chuyển hướng đến trang đăng nhập nếu chưa đăng nhập
//     exit();
// }
?> 

<!DOCTYPE html>
<html>
<head>
    <title>Trang chủ</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
<div class="container">
    <h2>Chào mừng, <?php echo $_SESSION['username']; ?>!</h2>
    <a href="tickets/list_tickets.php">Xem danh sách vé</a><br>
    <a href="tickets/confirm_reservation.php">Danh sách vé đã đặt</a><br>
    <a href="auth/logout.php" class="logout">Đăng xuất</a>
</div>


    
</body>
</html>
