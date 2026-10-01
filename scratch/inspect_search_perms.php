<?php
$conn = new mysqli('localhost', 'root', '', 'jiyaji_collection');
$res = $conn->query("
    SELECT r.id, r.name, r.display_name, p.module, p.action 
    FROM roles r 
    JOIN role_permissions rp ON r.id = rp.role_id 
    JOIN permissions p ON rp.permission_id = p.id 
    WHERE p.module IN ('analytics', 'search')
    ORDER BY r.id, p.module, p.action
");
echo "=== ROLES WITH ANALYTICS OR SEARCH PERMISSIONS ===\n";
while ($row = $res->fetch_assoc()) {
    echo sprintf("Role [%d: %s (%s)] -> %s:%s\n", $row['id'], $row['name'], $row['display_name'], $row['module'], $row['action']);
}

$pRes = $conn->query("SELECT * FROM permissions WHERE module IN ('analytics', 'search')");
echo "\n=== PERMISSIONS IN DB ===\n";
while ($p = $pRes->fetch_assoc()) {
    echo sprintf("[%d] %s:%s (%s)\n", $p['id'], $p['module'], $p['action'], $p['label'] ?? '');
}
