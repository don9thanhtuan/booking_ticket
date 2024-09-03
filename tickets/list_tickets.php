<?php
session_start(); // Bắt đầu session

// Kết nối đến cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'ticket'); // Đảm bảo tên cơ sở dữ liệu là chính xác

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Truy vấn lấy danh sách vé
$sql = "SELECT * FROM tickets"; // Giả sử bảng vé có tên là `tickets`
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách vé</title>
    <link rel="stylesheet" href="../css/list_tickets.css"> <!-- Đường dẫn đến file CSS -->
</head>
<body>
    <h2>Danh sách vé</h2>
    
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Tên vé</th>
                    <th>Giá</th>
                    <th>Số lượng còn lại</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($ticket = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ticket['name']); ?></td>
                        <td><?php echo number_format($ticket['price'], 0, ',', '.'); ?> VND</td>
                        <td><?php echo $ticket['quantity']; ?></td>
                        <td><?php echo ($ticket['quantity'] > 0) ? 'Còn vé' : 'Hết vé'; ?></td>
                        <td>
                            <?php if ($ticket['quantity'] > 0): ?>
                                <form action="reserve_ticket.php" method="get" style="display:inline;">
                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>">
                                    <input type="submit" value="Đặt vé">
                                </form>
                            <?php else: ?>
                                Không khả dụng
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Không có vé nào khả dụng.</p>
    <?php endif; ?>
    
    <?php $conn->close(); // Đóng kết nối cơ sở dữ liệu ?>
</body>
</html>

