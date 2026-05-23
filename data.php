<?php
// MySQL ulanish
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "test";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("❌ Ulanishda xato: " . $conn->connect_error);
}

/**
 * 1️⃣ MySQL → JSON fayl
 */
function mysqlToJsonFile($conn, $table, $jsonFile) {
    $result = $conn->query("SELECT * FROM `$table`");
    if (!$result) {
        return "❌ Jadval topilmadi: " . $conn->error;
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $json = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($jsonFile, $json);

    return "✅ $table jadvali $jsonFile faylga yozildi!";
}

/**
 * 2️⃣ JSON fayl → MySQL
 */
function jsonFileToMysql($conn, $table, $jsonFile) {
    if (!file_exists($jsonFile)) {
        return "❌ $jsonFile topilmadi!";
    }

    $json = file_get_contents($jsonFile);
    $data = json_decode($json, true);

    if (!$data) {
        return "❌ JSON fayl noto‘g‘ri yoki bo‘sh!";
    }

    foreach ($data as $row) {
        $columns = implode("`, `", array_keys($row));
        $values  = implode("', '", array_map([$conn, 'real_escape_string'], array_values($row)));

        $sql = "INSERT INTO `$table` (`$columns`) VALUES ('$values')";
        if (!$conn->query($sql)) {
            echo "❌ Xato: " . $conn->error . "\n";
        }
    }

    return "✅ $jsonFile fayldan $table jadvaliga yozildi!";
}