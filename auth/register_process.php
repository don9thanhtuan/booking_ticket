<?php
$conn = new mysqli('localhost', 'root', '', 'ticket');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_POST['username'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Mã hóa mật khẩu

// Kiểm tra xem người dùng đã tồn tại hay chưa
$sql_check = "SELECT * FROM users WHERE username='$username' OR email='$email'";
$result = $conn->query($sql_check);

if ($result->num_rows > 0) {
    echo "Tên đăng nhập hoặc email đã được sử dụng!";
} else {
    // Thêm người dùng mới vào cơ sở dữ liệu
    $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
    if ($conn->query($sql) === TRUE) {
        echo "Đăng ký thành công!";
        header("Location: login.php"); // Chuyển hướng tới trang đăng nhập
    } else {
        echo "Đã xảy ra lỗi: " . $conn->error;
    }
}
$conn->close();
?>
