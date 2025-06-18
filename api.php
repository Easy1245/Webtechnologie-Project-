<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

$dbConfig = [
    'host' => 'localhost',
    'port' => '5432',
    'dbname' => 'webtechhelp',
    'user' => 'admin',
    'password' => 'webtech is bon'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    header('Content-Type: application/json');

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        echo json_encode(['success' => false, 'message' => 'Vul gebruikersnaam en wachtwoord in']);
        exit;
    }

    try {
        $pdo = new PDO(
            "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}",
            $dbConfig['user'],
            $dbConfig['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                echo json_encode(['success' => true]);
                exit;
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Ongeldige gebruikersnaam of wachtwoord',
                    'debug' => 'Wachtwoord klopt niet'
                ]);
                exit;
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Ongeldige gebruikersnaam of wachtwoord',
                'debug' => 'Gebruiker niet gevonden'
            ]);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database fout: ' . $e->getMessage()]);
        exit;
    }
}

// Nieuwe GET actie: get_weather_data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'get_weather_data') {
    header('Content-Type: application/json');

    try {
        $pdo = new PDO(
            "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}",
            $dbConfig['user'],
            $dbConfig['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Haal laatste 50 records op, met alle velden die je nodig hebt
        $stmt = $pdo->query('SELECT created_at, temperature, humidity, weather_condition FROM weather_data ORDER BY created_at DESC LIMIT 50');
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Keer om zodat oudste eerst (voor grafiek) en stuur terug
        echo json_encode(array_reverse($data));
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database fout: ' . $e->getMessage()]);
        exit;
    }
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Methode niet toegestaan']);
