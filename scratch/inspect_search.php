<?php
$conn = new mysqli('localhost', 'root', '', 'jiyaji_collection');
$res = $conn->query('SHOW INDEX FROM search_logs');
echo "Indexes on search_logs:\n";
while ($row = $res->fetch_assoc()) {
    echo "  " . $row['Key_name'] . ' -> ' . $row['Column_name'] . "\n";
}
