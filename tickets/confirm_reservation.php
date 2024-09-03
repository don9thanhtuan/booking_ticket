<?php
// Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ticket";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Lấy thông tin người dùng từ session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    die("Chưa đăng nhập. Vui lòng đăng nhập để xem thông tin đặt vé.");
}

$username = $_SESSION['username'];

// Truy vấn để lấy user_id từ username
$sql = "SELECT user_id FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_id = $user['user_id'];
} else {
    die("Người dùng không tồn tại.");
}

// Truy vấn cơ sở dữ liệu để lấy thông tin đặt vé của người dùng, loại trừ vé đã hủy
$sql = "SELECT * FROM bookings 
        INNER JOIN tickets ON bookings.ticket_id = tickets.ticket_id 
        WHERE user_id = ? AND bookings.status != 'cancelled'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Reservation</title>
    <link rel="stylesheet" href="../css/confirm_reservation.css">
</head>
<body>
    <div class="container">
        <h1>Thông Tin Đặt Vé</h1>
        <?php
        if ($result->num_rows > 0) {
            // Hiển thị thông tin đặt vé
            while($row = $result->fetch_assoc()) {
                echo "<div class='booking-info'>";
                echo "<p><span>Ticket Name:</span> " . htmlspecialchars($row["name"]) . "</p>";
                echo "<p><span>Price:</span> " . number_format($row["price"], 2) . " VND</p>";
                echo "<p><span>Booking Time:</span> " . htmlspecialchars($row["booking_time"]) . "</p>";
                echo "<p><span>Status:</span> " . htmlspecialchars($row["status"]) . "</p>";
                echo "<form action='cancel_reservation.php' method='POST'>";
                echo "<input type='hidden' name='booking_id' value='" . htmlspecialchars($row["booking_id"]) . "'>";
                echo "<button type='submit' class='btn-cancel'>Hủy vé</button>";
                echo "<p style='font-style: italic;'>Khi hủy vé quý khách sẽ nhận được số tiền bằng 90% giá vé!</p>";
                echo "</form>";
                echo "</div>";
            }
        } else {
            echo "<p>Không có thông tin đặt vé.</p>";
        }
        ?>
    </div>
</body>
</html>

<?php
// Đóng kết nối
$stmt->close();
$conn->close();
?>
