<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
date_default_timezone_set('Europe/Amsterdam');

// DATABASE GEGEVENS
$dbHost = 'localhost';
$dbPort = '5432';
$dbName = 'webtechhelp';
$dbUser = 'WebTechUser';
$dbPassword = 'webtech is bon';

$errorMessage = '';
$connectionError = '';
$loginSuccess = false;

try {
    $dsn = "pgsql:host=$dbHost;port=$dbPort;dbname=$dbName";
    $pdo = new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    $errorMessage = "Databasefout: " . $e->getMessage();
}

if (!$connectionError && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputUsername = $_POST['username'] ?? '';
    $inputPassword = $_POST['password'] ?? '';

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $inputUsername]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['password'] === $inputPassword) {
            $_SESSION['username'] = $user['username'];
            $loginSuccess = true;
        } else {
            $errorMessage = "Fout: ongeldige gebruikersnaam of wachtwoord.";
        }
    } catch (PDOException $e) {
        $errorMessage = "Er is iets misgegaan bij het inloggen.";
    }
}

$stickman = "
      O
     /|\\
     / \\
    Féë
";
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Féë's Stickman Pagina</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-purple-500 to-pink-500 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-xl text-center max-w-md w-full">
        <h1 class="text-4xl font-bold text-purple-700 mb-4">Maak kennis met Féë!</h1>

        <pre class="text-lg font-mono bg-gray-100 p-4 rounded-lg"><?php echo htmlspecialchars($stickman); ?></pre>

        <?php if ($connectionError): ?>
            <p class="text-red-500 mt-4 font-semibold"><?= $connectionError ?></p>
        <?php elseif ($loginSuccess): ?>
            <div class="mt-6">
                <p class="text-green-600 mb-4 font-semibold">Welkom, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
                
                <!-- Toegevoegde afbeelding na inloggen -->
                <div class="bg-gray-100 p-4 rounded-lg">
                    <img src="data:image/jpeg;base64,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" 
                         alt="Welkomstafbeelding"
                         class="rounded-lg mx-auto">
                    <p class="mt-2 text-gray-700">Je bent succesvol ingelogd!</p>
                </div>
                
                <!-- Uitlogknop -->
                <form method="POST" action="?logout=1" class="mt-4">
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">
                        Uitloggen
                    </button>
                </form>
            </div>
        <?php else: ?>
            <?php if ($errorMessage): ?>
                <p class="text-red-500 mt-4 font-semibold"><?= $errorMessage ?></p>
            <?php endif; ?>

            <form method="POST" class="mt-6 text-left">
                <label class="block mb-2 text-sm font-bold text-gray-700">Gebruikersnaam:</label>
                <input type="text" name="username" required class="w-full px-4 py-2 mb-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400">

                <label class="block mb-2 text-sm font-bold text-gray-700">Wachtwoord:</label>
                <input type="password" name="password" required class="w-full px-4 py-2 mb-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400">

                <button type="submit" class="w-full bg-purple-600 text-white font-bold py-2 rounded hover:bg-purple-700 transition">Inloggen</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
