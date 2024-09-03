<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hủy vé</title>
    <link rel="stylesheet" href="../css/cancel_reservation.css">
</head>
<body>
    
</body>
</html>
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy thông tin từ form
    $booking_id = $_POST['booking_id'] ?? null;

    // Kiểm tra xem booking_id có tồn tại không
    if (!$booking_id) {
        die("Không tìm thấy thông tin đặt vé.");
    }

    // Xử lý khi người dùng đã gửi số tài khoản để hoàn tiền
    if (isset($_POST['account_number'])) {
        $account_number = $_POST['account_number'];
        $bank_name = $_POST['bank_name'];

        // Bắt đầu giao dịch
        $conn->begin_transaction();
        
        try {
            // Truy vấn để lấy ticket_id từ booking_id
            $sql = "SELECT ticket_id FROM bookings WHERE booking_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $booking = $result->fetch_assoc();
                $ticket_id = $booking['ticket_id'];
                
                // Cập nhật số vé có sẵn
                $sql = "UPDATE tickets SET quantity = quantity + 1 WHERE ticket_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $ticket_id);
                $stmt->execute();

                // Cập nhật trạng thái đặt vé thành "cancelled"
                $sql = "UPDATE bookings SET status = 'Cancelled' WHERE booking_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $booking_id);
                $stmt->execute();

                // Kiểm tra nếu tất cả các câu lệnh SQL thực thi thành công
                if ($stmt->affected_rows > 0) {
                    // Commit giao dịch
                    $conn->commit();
                    echo "<p>Vé đã được hủy. Số tiền sẽ được hoàn về tài khoản: $account_number Ngân hàng: $bank_name</p>";
                } else {
                    // Rollback giao dịch nếu có lỗi
                    $conn->rollback();
                    echo "<p>Không thể hủy vé. Vui lòng thử lại.</p>";
                }
            } else {
                echo "<p>Không tìm thấy thông tin đặt vé.</p>";
            }
        } catch (Exception $e) {
            // Rollback giao dịch nếu có ngoại lệ
            $conn->rollback();
            echo "<p>Đã xảy ra lỗi: " . htmlspecialchars($e->getMessage()) . ". Vui lòng thử lại sau.</p>";
        }
    } else {
        // Hiển thị form để nhập số tài khoản
        echo "<form action='' method='POST'>";
        echo "<input type='hidden' name='booking_id' value='$booking_id'>";
        echo "<label for='account_number'>Số tài khoản để hoàn tiền: </label>";
        echo "<input type='text' name='account_number' required>";
        echo "<label for='bank_name'>Tên ngân hàng: </label>";
        echo "<input type='text' name='bank_name' required>";
        echo "<button type='submit'>Xác nhận hoàn tiền</button>";
        echo "</form>";
    }
}

// Đóng kết nối
$conn->close();
?>
