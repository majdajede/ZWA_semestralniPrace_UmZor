<?php
/**
 * This script handles the profile update functionality for a user.
 * 
 * It performs the following tasks:
 * - Checks if the user is logged in, if not redirects to the login page.
 * - Loads user data from a JSON file.
 * - Validates and updates user profile information including username, email, and password.
 * - Updates the username in the posts file if the username is changed.
 * - Displays a form for the user to update their profile information.
 * 
 * @package UserProfileUpdate
 * 
 * @file /home/lebedmag/www/semestralka/changeProfil.php
 * 
 * @requires funkce.php
 * @requires headerHtml.php
 * @requires footer.php
 * 
 * @uses json_decode()
 * @uses json_encode()
 * @uses file_get_contents()
 * @uses file_put_contents()
 * @uses password_hash()
 * @uses filter_var()
 * @uses session_start()
 * @uses bin2hex()
 * @uses random_bytes()
 * 
 * @var array $usersData Array of user data loaded from users.json
 * @var string $currentUsername The username of the currently logged-in user
 * @var int|null $currentUserIndex The index of the current user in the usersData array
 * @var array|null $currentUserData The data of the currently logged-in user
 * @var string $username The new username submitted by the user
 * @var string $email The new email submitted by the user
 * @var array $errors Array of validation errors
 * @var string $newPasswordHash The hashed password to be saved
 * 
 * @const string $usersFile Path to the users JSON file
 * @const string $postsFile Path to the posts JSON file
 * 
 * @return void
 */
require_once 'funkce.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$usersFile = 'users.json';
if (!file_exists($usersFile)) {
    die("Chyba serveru: Databáze uživatelů nebyla nalezena.");
}
$usersData = json_decode(file_get_contents($usersFile), true);
if ($usersData === null) {
    die("Chyba serveru: Nepodařilo se načíst data uživatelů.");
}

$currentUsername = $_SESSION['username'];
$currentUserIndex = null;
$currentUserData = null;

foreach ($usersData as $idx => $u) {
    if ($u['username'] === $currentUsername) {
        $currentUserIndex = $idx;
        $currentUserData = $u;
        break;
    }
}

if ($currentUserData === null) {
    die("Chyba: Uživatel nebyl nalezen v databázi.");
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$username = $currentUserData['username'];
$email    = $currentUserData['email'];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }

    $oldUsername = $username;  
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (strlen($username) > 16) {
        $errors['username'] = 'Uživatelské jméno musí mít maximálně 16 znaků.';
    }
    if (strlen($username) < 3) {
        $errors['username'] = 'Uživatelské jméno musí mít alespoň 3 znaky.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Neplatný formát emailu.';
    }

    foreach ($usersData as $idx => $u) {
        if ($idx !== $currentUserIndex) {
            if ($u['username'] === $username && $username !== $currentUserData['username']) {
                $errors['username'] = 'Uživatelské jméno je již obsazeno.';
            }
            if ($u['email'] === $email && $email !== $currentUserData['email']) {
                $errors['email'] = 'Email je již použit.';
            }
        }
    }

    $newPasswordHash = $currentUserData['password'];
    if ($password !== '' || $confirm_password !== '') {
        if (strlen($password) < 6) {
            $errors['password'] = 'Heslo musí mít alespoň 6 znaků.';
        } elseif (strlen($password) > 30) {
            $errors['password'] = 'Heslo nesmí mít více než 30 znaků.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors['password'] = 'Heslo musí obsahovat alespoň jedno velké písmeno.';
        } elseif (!preg_match('/[a-z]/', $password)) {
            $errors['password'] = 'Heslo musí obsahovat alespoň jedno malé písmeno.';
        } elseif (!preg_match('/\d/', $password)) {
            $errors['password'] = 'Heslo musí obsahovat alespoň jedno číslo.';
        }

        if ($password !== $confirm_password) {
            $errors['confirm_password'] = 'Hesla se neshodují.';
        }

        if (empty($errors['password']) && empty($errors['confirm_password'])) {
            $newPasswordHash = password_hash($password, PASSWORD_DEFAULT);
        }
    }

    if (empty($errors)) {
        $usersData[$currentUserIndex]['username'] = $username;
        $usersData[$currentUserIndex]['email']    = $email;
        $usersData[$currentUserIndex]['password'] = $newPasswordHash;

        file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if ($username !== $oldUsername) {
            $postsFile = 'prispevky.json';
            if (file_exists($postsFile)) {
                $posts = json_decode(file_get_contents($postsFile), true);
                if (is_array($posts)) {
                    foreach ($posts as &$p) {
                        if (isset($p['author']) && $p['author'] === $oldUsername) {
                            $p['author'] = $username;
                        }
                    }
                    file_put_contents($postsFile, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
            }
        }
        $_SESSION['username'] = $username;
        header("Location: profil.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Úprava profilu</title>
    <script src="js/changeProfil.js" defer></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="css/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php require 'headerHtml.php'; ?>

    <div class="center">
        <h1>Upravit profil</h1>

        <form method="POST" action="changeProfil.php" class="form-container">
            <input type="hidden" name="csrf_token" value="<?= escape($_SESSION['csrf_token']) ?>">

            <div>
                <label for="username">Uživatelské jméno:</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    autocomplete="username"
                    required
                    value="<?= isset($errors) ? escape($username) : escape($currentUserData['username']) ?>"
                    maxlength="16"
                    data-old-username="<?= escape($currentUserData['username']) ?>"
                >
                <?= isset($errors['username']) ? '<p class="error">' . escape($errors['username']) . '</p>' : '' ?>
            </div>

            <div>
                <label for="email">Email:</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    autocomplete="email"
                    value="<?= isset($errors) ? escape($email) : escape($currentUserData['email']) ?>"
                    data-old-email="<?= escape($currentUserData['email']) ?>"
                >
                <?= isset($errors['email']) ? '<p class="error">' . escape($errors['email']) . '</p>' : '' ?>
            </div>

            <div>
                <label for="password">Heslo (pokud chcete změnit):</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    autocomplete="new-password"
                >
                <?= isset($errors['password']) ? '<p class="error">' . escape($errors['password']) . '</p>' : '' ?>
            </div>

            <div>
                <label for="confirm_password">Znovu heslo (pokud chcete změnit):</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password"
                    autocomplete="new-password"
                >
                <?= isset($errors['confirm_password']) ? '<p class="error">' . escape($errors['confirm_password']) . '</p>' : '' ?>
            </div>

            <p>Všechna pole krom hesla jsou povinná</p>
            <p>(tj. pokud nevyplníte heslo, zůstane vám staré)</p>
            <div>
                <button type="submit">Uložit změny</button>
            </div>
        </form>
    </div>

    <?php require 'footer.php'; ?>
</body>
</html>
