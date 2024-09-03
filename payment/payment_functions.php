<?php
function checkPaymentStatus($transactionId)
{
    $apiKey = 'AK_CS.46e980e0683811ef9eef9daee9cc4b4e.yTsR78SW658JwRwEK2DdvoSE40Bu0Q97sYQ16oAP2DANXWmxlLg8O0I2RUreka4JHFa6A7SY';
    $apiUrl = 'https://oauth.casso.vn/v2/transactions';

    // Cấu hình cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl . '?' . http_build_query(['transaction_id' => $transactionId]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Apikey ' . $apiKey
    ]);

    // Thực thi yêu cầu cURL
    $response = curl_exec($ch);

    // Kiểm tra lỗi
    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }

    // Đóng cURL
    curl_close($ch);

    // Phân tích kết quả từ API
    $result = json_decode($response, true);

    // Kiểm tra trạng thái thanh toán
    if (isset($result['error']) && $result['error'] == 0 && isset($result['data']['totalRecords']) && $result['data']['totalRecords'] > 0) {
        return true; // Thanh toán thành công
    } else {
        echo 'Thanh toán chưa hoàn tất hoặc thất bại. Vui lòng thử lại.';
        return false; // Không có giao dịch hoặc lỗi
    }
}

function saveBookingToDatabase($ticket_id, $user_id, $transactionId)
{
    // Thông tin kết nối cơ sở dữ liệu
    $servername = "localhost";
    $username = "root"; // thay bằng username của bạn
    $password = ""; // thay bằng password của bạn
    $dbname = "ticket"; // thay bằng tên cơ sở dữ liệu của bạn

    // Tạo kết nối
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Kiểm tra kết nối
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Chuẩn bị câu lệnh SQL để chèn dữ liệu
    $booking_time = date("Y-m-d H:i:s");
    $confirmation_time = date("Y-m-d H:i:s");
    $status = 'confirmed'; // thay đổi giá trị theo trạng thái của bạn

    // Sử dụng Prepared Statements để tránh SQL Injection
    $stmt = $conn->prepare("INSERT INTO bookings (ticket_id, user_id, booking_time, confirmation_time, status) 
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $ticket_id, $user_id, $booking_time, $confirmation_time, $status);

    if ($stmt->execute()) {
        echo "Dữ liệu đã được lưu thành công!";
    } else {
        echo "Lỗi: " . $stmt->error;
    }

    // Đóng kết nối
    $stmt->close();
    $conn->close();
}
?>
