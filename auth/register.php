<!-- register.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Đăng ký</title>
    <link rel="stylesheet" href="../css/register.css">
</head>
<body>
    <h2>Đăng ký tài khoản</h2>
    <form action="register_process.php" method="post">
        <label for="username">Tên đăng nhập:</label>
        <input type="text" name="username" required><br><br>
        <label for="email">Email:</label>
        <input type="email" name="email" required><br><br>
        <label for="password">Mật khẩu:</label>
        <input type="password" name="password" required><br><br>
        <input type="submit" value="Đăng ký">
    </form>
</body>
</html>
