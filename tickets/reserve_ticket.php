<?php
session_start(); // Bắt đầu session

include '../payment/payment_functions.php'; // Bao gồm file chứa hàm kiểm tra thanh toán

// Kết nối đến cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'ticket');

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Nhận `ticket_id` và `transaction_id` từ phương thức GET
$ticket_id = $_GET['ticket_id'] ?? null;
$transactionId = $_GET['transaction_id'] ?? null;

if (!isset($_SESSION['username'])) {
    die("Chưa đăng nhập. Vui lòng đăng nhập để đặt vé.");
}

// Lấy thông tin người dùng từ session
$username = $_SESSION['username'];

// Truy vấn lấy `user_id` từ `username`
$sql = "SELECT user_id FROM Users WHERE username = ?";
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

// Truy vấn lấy thông tin vé dựa trên `ticket_id`
$sql = "SELECT price, name, quantity FROM tickets WHERE ticket_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();
$ticket = $result->fetch_assoc();

if (!$ticket) {
    die("Không tìm thấy vé với ID này.");
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $transactionId) {
    // Kiểm tra trạng thái thanh toán
    if (checkPaymentStatus($transactionId)) {
        // Nếu thanh toán thành công, tiến hành cập nhật số lượng vé và lưu thông tin đặt vé

        if ($ticket['quantity'] > 0) {
            // Bắt đầu giao dịch
            $conn->begin_transaction();

            try {
                // Cập nhật số lượng vé còn lại
                $new_quantity = $ticket['quantity'] - 1;
                $sql = "UPDATE tickets SET quantity = ? WHERE ticket_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $new_quantity, $ticket_id);
                $stmt->execute();
                
                // Kiểm tra xem số hàng bị ảnh hưởng có đúng không
                if ($stmt->affected_rows > 0) {
                    $stmt->close();

                    // Lưu thông tin đặt vé vào cơ sở dữ liệu
                    $booking_time = date("Y-m-d H:i:s");
                    $confirmation_time = date("Y-m-d H:i:s");
                    $status = 'confirmed'; // Hoặc trạng thái khác nếu cần

                    $sql = "INSERT INTO bookings (ticket_id, user_id, booking_time, confirmation_time, status) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iisss", $ticket_id, $user_id, $booking_time, $confirmation_time, $status);
                    $stmt->execute();
                    
                    if ($stmt->affected_rows > 0) {
                        $stmt->close();

                        // Cam kết giao dịch
                        $conn->commit();

                        // Chuyển hướng đến trang xác nhận
                        header("Location: confirm_reservation.php");
                        exit();
                    } else {
                        throw new Exception("Không thể lưu thông tin đặt vé.");
                    }
                } else {
                    throw new Exception("Không thể cập nhật số lượng vé.");
                }
            } catch (Exception $e) {
                // Nếu có lỗi, rollback giao dịch
                $conn->rollback();
                echo "Đã xảy ra lỗi: " . $e->getMessage();
            }
        } else {
            echo "Số lượng vé không đủ.";
        }
    } else {
        echo "Thanh toán chưa hoàn tất hoặc thất bại. Vui lòng thử lại.";
    }
}

// Đặt thời gian hết hạn (5 phút từ thời điểm hiện tại)
$expiryTime = time() + 5 * 60;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán</title>
    <link rel="stylesheet" href="/css/reserve_ticket.css">
    <script>
        function startCountdown(expiryTime) {
            var countdownElement = document.getElementById('countdown');
            var messageElement = document.getElementById('message');
            var interval = setInterval(function() {
                var now = new Date().getTime();
                var distance = expiryTime - now;

                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                countdownElement.innerHTML = minutes + " phút " + seconds + " giây ";

                if (distance < 0) {
                    clearInterval(interval);
                    countdownElement.innerHTML = "0 phút 0 giây";
                    messageElement.innerHTML = "Vé của bạn đã bị hủy!";
                }
            }, 1000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            var expiryTime = <?php echo $expiryTime * 1000; ?>;
            startCountdown(expiryTime);
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>Vui lòng thanh toán để đặt vé!</h1>
        <p>Chuyển khoản đến tài khoản ngân hàng: MB 0866629913 | Đồng Thanh Tuấn | 
        <?php echo number_format($ticket['price'], 0, ',', '.'); ?> VND</p>
        <p>Vé sẽ tự động hủy khi quý khách chưa thanh toán trong <span id="countdown" style="font-weight: bold; color: #e74c3c;">5 phút 0 giây</span></p>
        <form method="GET" action="">
            <label for="transaction_id">Mã giao dịch:</label>
            <input type="text" name="transaction_id" required>
            <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
            <input type="submit" value="Kiểm tra thanh toán">
        </form>
        <span id="message" style="display: block; font-size: 14px; color: #e74c3c; text-align: center; margin-top: 20px;"></span>
    </div>
</body>
</html>
