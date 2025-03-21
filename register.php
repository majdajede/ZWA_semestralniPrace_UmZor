<?php
/**
 * This script handles user registration.
 * 
 * It performs the following tasks:
 * - Starts a session and includes necessary functions.
 * - Generates a CSRF token if it doesn't exist.
 * - Validates the registration form data when submitted via POST request.
 * - Checks for errors such as:
 *   - Username length and uniqueness.
 *   - Email format and uniqueness.
 *   - Password length, complexity, and confirmation match.
 * - If there are no errors, it saves the new user to a JSON file and redirects to the index page.
 * 
 * The HTML part of the script includes:
 * - A registration form with fields for username, email, password, and password confirmation.
 * - Error messages for each field if validation fails.
 * - Links to external CSS and JavaScript files.
 * - Inclusion of header and footer HTML.
 */
session_start();
require_once 'funkce.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    $usersFile = 'users.json';
    $users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

    if (strlen($username) > 16) {
        $errors['username'] = 'Uživatelské jméno musí mít maximálně 16 znaků.';
    }
    if (strlen($username) < 3) {
        $errors['username'] = 'Uživatelské jméno musí mít alespoň 3 znaky.';
    }
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            $errors['username'] = 'Uživatelské jméno je již obsazeno.';
        }
        if ($user['email'] === $email) {
            $errors['email'] = 'Email je již použit.';
        }
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Neplatný formát emailu.';
    }

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

    if (empty($errors)) {
        $users[] = [
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'user'
        ];
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'user';
        header('Location: index.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="js/register.js" defer></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="css/favicon.ico" type="image/x-icon">
    <title>Registrace</title>
</head>
<body>
    <?php require 'headerHtml.php'; ?>
    <div class="center">
            <h1>Registrace</h1>
            <form method="POST" action="register.php" class="form-container">
                <input type="hidden" name="csrf_token" value="<?= escape($_SESSION['csrf_token']) ?>">

                <div>
                    <label for="username">Uživatelské jméno:</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required
                        value="<?= escape($_POST['username'] ?? '') ?>"
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
                        value="<?= escape($_POST['email'] ?? '') ?>"
                    >
                    <?= isset($errors['email']) ? '<p class="error">' . escape($errors['email']) . '</p>' : '' ?>
                </div>

                <div>
                    <label for="password">Heslo:</label>
                    <input type="password" id="password" name="password" required>
                    <?= isset($errors['password']) ? '<p class="error">' . escape($errors['password']) . '</p>' : '' ?>
                </div>

                <div>
                    <label for="confirm_password">Znovu heslo:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <?= isset($errors['confirm_password']) ? '<p class="error">' . escape($errors['confirm_password']) . '</p>' : '' ?>
                </div>
                <p>Všechna pole jsou povinná</p>
                <p>Máte již účet? <a href="login.php">Přihlaste se zde</a>.</p>

                <div>
                    <button type="submit">Zaregistrovat se</button>
                </div>

                <?= isset($errors['csrf']) ? '<p class="error">' . escape($errors['csrf']) . '</p>' : '' ?>
            </form>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
