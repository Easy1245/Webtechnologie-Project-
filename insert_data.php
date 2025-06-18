<?php
header('Content-Type: application/json');

$dbConfig = [
    'host' => 'localhost',
    'port' => '5432',
    'dbname' => 'webtechhelp',
    'user' => 'admin',
    'password' => 'webtech is bon'
];

try {
    $pdo = new PDO(
        "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}",
        $dbConfig['user'],
        $dbConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $temperature = filter_input(INPUT_POST, 'temperature', FILTER_VALIDATE_FLOAT);
    $humidity = filter_input(INPUT_POST, 'humidity', FILTER_VALIDATE_INT);
    $condition = filter_input(INPUT_POST, 'condition', FILTER_SANITIZE_STRING);

    if ($temperature === false || $humidity === false || empty($condition)) {
        throw new Exception("Ongeldige invoer.");
    }

    $stmt = $pdo->prepare("INSERT INTO weather_data (temperature, humidity, weather_condition, date) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$temperature, $humidity, $condition]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
