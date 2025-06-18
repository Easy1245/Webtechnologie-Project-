<?php
$dbConfig = [
    'host' => 'localhost',
    'port' => '5432',
    'dbname' => 'webtechhelp',
    'user' => 'admin',
    'password' => 'webtech is bon'
];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    header('Content-Type: application/json');

    try {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            throw new Exception("Ongeldige ID.");
        }

        $pdo = new PDO(
            "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}",
            $dbConfig['user'],
            $dbConfig['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $stmt = $pdo->prepare("DELETE FROM weather_data WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

try {
    $pdo = new PDO(
        "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}",
        $dbConfig['user'],
        $dbConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->query("SELECT id, temperature, humidity, weather_condition, date FROM weather_data ORDER BY date DESC");


    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $iconUrl = "";
        switch ($row['weather_condition']) {
            case "Zonnig":
                $iconUrl = "https://wilmavervoort.nl/wp-content/uploads/2018/07/zonnetje.png";
                break;
            case "Bewolkt":
                $iconUrl = "https://static.vecteezy.com/ti/gratis-vector/p1/7277681-cartoon-wolk-met-gezicht-emoties-gratis-vector.jpg";
                break;
            case "Regen":
                $iconUrl = "https://static.vecteezy.com/ti/gratis-vector/p1/6490785-boze-wolk-met-regenillustratie-voor-kinderen-vector.jpg";
                break;
            default:
                $iconUrl = "";
        }

        echo "<div class='weather-entry'>";
        echo "<h3>" . htmlspecialchars($row['date']) . "</h3>";
        if ($iconUrl !== "") {
            echo "<img src='" . htmlspecialchars($iconUrl) . "' alt='icon' style='width:60px; height:60px;' />";
        }
        echo "<p><strong>Temperatuur:</strong> " . htmlspecialchars($row['temperature']) . " Â°C</p>";
        echo "<p><strong>Vochtigheid:</strong> " . htmlspecialchars($row['humidity']) . "%</p>";
        echo "<p><strong>Weer:</strong> " . htmlspecialchars($row['weather_condition']) . "</p>";
        echo "<form onsubmit='return deleteEntry(" . $row['id'] . ")' style='margin-top:1em;'>
        <button type='submit' style='background:#ff5555;color:white;padding:0.5em 1em;border:none;border-radius:5px;cursor:pointer;'>Verwijder</button>
        </form>";
        echo "</div>";

    }
} catch (PDOException $e) {
    echo "Fout bij laden data: " . $e->getMessage();
}
?>
