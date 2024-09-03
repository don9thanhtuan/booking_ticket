<!DOCTYPE html>
<html>

<head>
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="../css/login.css">
</head>

<body>
    <h2>Đăng nhập</h2>
    <div class="login" style="    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
    margin: 0 auto;">
        <form action="login_process.php" method="post">
            <label for="username">Tên đăng nhập:</label>
            <input type="text" name="username" required><br><br>
            <label for="password">Mật khẩu:</label>
            <input type="password" name="password" required><br><br>
            <input type="submit" value="Đăng nhập">
        </form>
        <div class="button-container">
            <a href="register.php" class="btn-register" style="width:95%">Đăng ký</a>
        </div>
    </div>
    <br>
</body>

</html>