<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DATABASE CONFIGURATIE
$dbConfig = [
    'host'     => 'localhost',
    'port'     => '5432',
    'dbname'   => 'webtechhelp',
    'user'     => 'admin',
    'password' => 'webtech is bon'
];

// DATABASE VERBINDING
try {
    $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    // Controleer of de tabel 'weather_data' bestaat, maak aan als dat niet zo is
try {
    $pdo->query("SELECT 1 FROM weather_data LIMIT 1");
} catch (PDOException $e) {
    // Tabel bestaat niet, dus maken we hem aan
    $createTableSQL = "
        CREATE TABLE weather_data (
            id SERIAL PRIMARY KEY,
            temperature DECIMAL(5,2) NOT NULL,
            humidity INT NOT NULL CHECK (humidity >= 0 AND humidity <= 100),
            weather_condition VARCHAR(50) NOT NULL,
            date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";
    $pdo->exec($createTableSQL);
}

} catch (PDOException $e) {
    die("<div style='color:red;padding:1rem;background:#ffebee;border-radius:5px;'>
            <h2>Databasefout!</h2>
            <p><strong>Fout:</strong> {$e->getMessage()}</p>
        </div>");
}

// LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    } else {
        $loginError = "Ongeldige inloggegevens";
    }
}

// TOEVOEGEN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_weather']) && isset($_SESSION['user_id'])) {
    $temp = filter_input(INPUT_POST, 'temperature', FILTER_VALIDATE_FLOAT);
    $humidity = filter_input(INPUT_POST, 'humidity', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 0, 'max_range' => 100]
    ]);
    $condition = htmlspecialchars($_POST['condition'] ?? '');

    if ($temp !== false && $humidity !== false && !empty($condition)) {
        $stmt = $pdo->prepare("INSERT INTO weather_data (temperature, humidity, weather_condition) VALUES (?, ?, ?)");
        $stmt->execute([$temp, $humidity, $condition]);
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}

// VERWIJDEREN
if (isset($_GET['delete']) && isset($_SESSION['user_id'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM weather_data WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// OPHALEN
$stmt = $pdo->prepare("SELECT * FROM weather_data ORDER BY date DESC LIMIT 7");
$stmt->execute();
$weatherData = $stmt->fetchAll();

// ICON FUNCTIE
function getWeatherIcon($condition) {
    switch (strtolower($condition)) {
        case 'zonnig':
            return 'https://wilmavervoort.nl/wp-content/uploads/2018/07/zonnetje.png';
        case 'bewolkt':
            return 'https://static.vecteezy.com/ti/gratis-vector/p1/7277681-cartoon-wolk-met-gezicht-emoties-gratis-vector.jpg';
        case 'regen':
            return 'https://static.vecteezy.com/ti/gratis-vector/p1/6490785-boze-wolk-met-regenillustratie-voor-kinderen-vector.jpg';
        default:
            return 'images/default.png';
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <title>Weer Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(to bottom, #87ceeb 0%, #f0f8ff 100%);
            overflow-x: hidden;
            min-height: 100vh;
        }
        @keyframes pulseSlow {
            0%, 100% { box-shadow: 0 0 8px #4ade80; }
            50% { box-shadow: 0 0 20px #22c55e; }
        }
        .animate-pulse-slow {
            animation: pulseSlow 2s infinite;
        }
    </style>
</head>
<body class="p-4">

<div class="max-w-6xl mx-auto">
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-6 text-center">Weer Dashboard Login</h1>
            <?php if (isset($loginError)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4"><?= $loginError ?></div>
            <?php endif; ?>
            <form method="POST" class="space-y-4">
                <input type="text" name="username" placeholder="Gebruikersnaam" required class="w-full px-4 py-2 border rounded-lg" />
                <input type="password" name="password" placeholder="Wachtwoord" required class="w-full px-4 py-2 border rounded-lg" />
                <button type="submit" name="login" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">Inloggen</button>
            </form>
        </div>
    <?php else: ?>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Weer Dashboard</h1>
            <div class="flex items-center gap-4">
                <div id="live-clock" class="text-xl font-mono bg-white text-blue-500 px-4 py-1 rounded-xl shadow border border-blue-400 "></div>
                <a href="?logout=1" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-red-600">Uitloggen</a>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4">Nieuwe Weerdata Toevoegen</h2>
            <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="number" step="0.1" name="temperature" placeholder="Temperatuur (°C)" required class="px-3 py-2 border rounded-lg" />
                <input type="number" name="humidity" min="0" max="100" placeholder="Vochtigheid (%)" required class="px-3 py-2 border rounded-lg" />
                <select name="condition" required class="px-3 py-2 border rounded-lg">
                    <option value="">Selecteer conditie</option>
                    <option value="Zonnig">Zonnig</option>
                    <option value="Bewolkt">Bewolkt</option>
                    <option value="Regen">Regen</option>
                </select>
                <div class="col-span-full">
                    <button type="submit" name="add_weather" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-green-600">Toevoegen</button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <?php foreach ($weatherData as $day): ?>
                <div class="bg-white p-4 rounded-lg shadow-md text-center relative">
                    <h3 class="font-bold text-lg mb-2"><?= date('d-m-Y', strtotime($day['date'])) ?></h3>
                    <img src="<?= getWeatherIcon($day['weather_condition']) ?>" class="w-16 h-16 mx-auto mb-2" alt="icon" />
                    <p class="text-3xl font-semibold mb-1"><?= htmlspecialchars($day['temperature']) ?>°C</p>
                    <p>Vochtigheid: <?= htmlspecialchars($day['humidity']) ?>%</p>
                    <p>Conditie: <?= htmlspecialchars($day['weather_condition']) ?></p>
                    <form method="GET" onsubmit="return confirm('Weet je zeker dat je deze entry wilt verwijderen?')" class="mt-3">
                        <input type="hidden" name="delete" value="<?= $day['id'] ?>">
                        <button type="submit" class="text-blue-500 hover:text-red-700">Verwijderen</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- LIVE KLOK -->
<script>
function updateClock() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    const s = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('live-clock').textContent = `${h}:${m}:${s}`;
}
setInterval(updateClock, 1000);
updateClock();
</script>

</body>
</html>
