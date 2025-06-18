<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
// Database connectie (gebruik je bestaande configuratie)
$dbConfig = [
    'host'     => 'localhost',
    'port'     => '5432',
    'dbname'   => 'webtechhelp',
    'user'     => 'admin',
    'password' => 'webtech is bon'
];

try {
    $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database verbinding mislukt']);
    exit;
}

// Eenvoudige authenticatie (check of ingelogd)
if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Niet ingelogd']);
    exit;
}

// Endpoint en ID bepalen
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// Veronderstel dat je api.php direct aanroept als bijvoorbeeld http://jouwdomein/api.php/weather/123
$path = parse_url($requestUri, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

if (count($pathParts) < 2 || $pathParts[0] !== 'api.php' || $pathParts[1] !== 'weather') {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint niet gevonden']);
    exit;
}

$id = $pathParts[2] ?? null;

// Functies voor CRUD operaties

function getWeather($pdo, $id = null) {
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM weather_data WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        if ($data) {
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Weerdata niet gevonden']);
        }
    } else {
        $stmt = $pdo->query("SELECT * FROM weather_data ORDER BY date DESC");
        $data = $stmt->fetchAll();
        echo json_encode($data);
    }
}

function createWeather($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['temperature'], $input['humidity'], $input['weather_condition'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Verplichte velden ontbreken']);
        return;
    }

    $stmt = $pdo->prepare("INSERT INTO weather_data (temperature, humidity, weather_condition) VALUES (?, ?, ?)");
    $success = $stmt->execute([
        $input['temperature'],
        $input['humidity'],
        $input['weather_condition']
    ]);

    if ($success) {
        http_response_code(201);
        echo json_encode(['message' => 'Weerdata toegevoegd', 'id' => $pdo->lastInsertId()]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Kon weerdata niet toevoegen']);
    }
}

function updateWeather($pdo, $id) {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID ontbreekt']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $fields = [];
    $values = [];

    if (isset($input['temperature'])) {
        $fields[] = "temperature = ?";
        $values[] = $input['temperature'];
    }
    if (isset($input['humidity'])) {
        $fields[] = "humidity = ?";
        $values[] = $input['humidity'];
    }
    if (isset($input['weather_condition'])) {
        $fields[] = "weather_condition = ?";
        $values[] = $input['weather_condition'];
    }

    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(['error' => 'Geen velden om te updaten']);
        return;
    }

    $values[] = $id;

    $sql = "UPDATE weather_data SET " . implode(", ", $fields) . " WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute($values);

    if ($success) {
        echo json_encode(['message' => 'Weerdata bijgewerkt']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Kon weerdata niet bijwerken']);
    }
}

function deleteWeather($pdo, $id) {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID ontbreekt']);
        return;
    }
    
    $stmt = $pdo->prepare("DELETE FROM weather_data WHERE id = ?");
    $success = $stmt->execute([$id]);

    if ($success) {
        echo json_encode(['message' => 'Weerdata verwijderd']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Kon weerdata niet verwijderen']);
    }
}


// Router
switch ($requestMethod) {
    case 'GET':
        getWeather($pdo, $id);
        break;
    case 'POST':
        createWeather($pdo);
        break;
    case 'PUT':
        updateWeather($pdo, $id);
        break;
    case 'DELETE':
        deleteWeather($pdo, $id);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Methode niet toegestaan']);
        break;
}
