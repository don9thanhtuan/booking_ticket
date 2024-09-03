<?php
session_start(); // Bắt đầu session

// Kết nối đến cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'ticket');

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy dữ liệu từ form đăng nhập
$username = $_POST['username'];
$password = $_POST['password'];

// Sử dụng Prepared Statements để tránh SQL Injection
$sql = $conn->prepare("SELECT * FROM users WHERE username = ?");
$sql->bind_param("s", $username); // "s" chỉ định kiểu dữ liệu là string
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Kiểm tra mật khẩu đã nhập với mật khẩu đã mã hóa lưu trong cơ sở dữ liệu
    if (password_verify($password, $user['password'])) {
        // Thông báo xác minh thành công
        echo "Mật khẩu đã được xác minh thành công.";
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        // Chuyển hướng tới trang chủ
        header("Location: /TICKET/index.php");
        exit(); // Dừng lại sau khi chuyển hướng
    } else {
        // Nếu mật khẩu sai, hiển thị mật khẩu nhập vào và mật khẩu trong CSDL để kiểm tra
        echo "Mật khẩu nhập vào: " . htmlspecialchars($password) . "<br>";
        echo "Mật khẩu trong CSDL: " . htmlspecialchars($user['password']) . "<br>";
        echo "<script>alert('Sai mật khẩu!'); window.location.href = 'auth/login.php';</script>";
    }
} else {
    // Nếu không tìm thấy tên đăng nhập, thông báo lỗi
    echo "<script>alert('Tên đăng nhập không tồn tại!'); window.location.href = 'auth/login.php';</script>";
}

// Đóng kết nối và chuẩn bị truy vấn
$sql->close();
$conn->close();
?>

<!-- Đoạn mã để cập nhật mật khẩu đã mã hóa nếu cần thiết -->
<?php
// Ví dụ cập nhật mật khẩu của người dùng với username cụ thể
if (isset($_POST['update_password'])) {
    $usernameToUpdate = $_POST['usernameToUpdate'];
    $plainPassword = $_POST['newPassword']; // Mật khẩu plain text cần mã hóa
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

    // Cập nhật mật khẩu đã mã hóa vào cơ sở dữ liệu
    $updateSql = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
    $updateSql->bind_param("ss", $hashedPassword, $usernameToUpdate);
    $updateSql->execute();
    $updateSql->close();

    echo "<script>alert('Mật khẩu đã được cập nhật!'); window.location.href = 'auth/login.php';</script>";
}
?>
