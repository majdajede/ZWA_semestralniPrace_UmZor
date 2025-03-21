<?php
/**
 * This script handles the login functionality for the application.
 * 
 * It starts a session, includes necessary functions, and processes the login form submission.
 * 
 * The script performs the following tasks:
 * - Starts a session.
 * - Includes the 'funkce.php' file for additional functions.
 * - Initializes error and username variables.
 * - Checks if the request method is POST.
 * - Validates the CSRF token.
 * - Retrieves and trims the username and password from the POST data.
 * - Validates that the username and password are not empty.
 * - Checks if the users.json file exists and is in a valid format.
 * - Iterates through the users to authenticate the provided credentials.
 * - Sets session variables and redirects to the index page upon successful authentication.
 * - Displays an error message if authentication fails.
 * 
 * The HTML part of the script includes:
 * - A form for the user to enter their username and password.
 * - CSRF token hidden input field.
 * - Error message display if any.
 * - Links to external CSS and JavaScript files.
 * - Links to the registration page.
 * - Includes header and footer HTML.
 */
session_start();
require_once 'funkce.php';

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Uživatelské jméno a heslo jsou povinné.';
    } else {
        $usersFile = 'users.json';
        if (!file_exists($usersFile)) {
            die('Chyba serveru: Soubor s uživateli nenalezen.');
        }

        $users = json_decode(file_get_contents($usersFile), true);
        if ($users === null) {
            die('Chyba serveru: Neplatný formát souboru s uživateli.');
        }

        $authenticated = false;
        foreach ($users as $user) {
            if ($user['username'] === $username && password_verify($password, $user['password'])) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header('Location: index.php');
                exit;
            }
        }

        $error = 'Špatně zadané uživatelské jméno nebo heslo.';
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="js/login.js" defer></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="css/favicon.ico" type="image/x-icon">
    <title>Přihlášení</title>
</head>
<body>
    <?php require 'headerHtml.php'; ?>
    <div class="center">
            <h1>Přihlášení</h1>

            <form method="POST" action="login.php" class="form-container">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                
                <?php if (!empty($error)): ?>
                <p class="error"><?= escape($error) ?></p>
            <?php endif; ?>

                <div>
                    <label for="username">Uživatelské jméno:</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="<?= isset($_POST['username']) ? escape($_POST['username']) : '' ?>" 
                        required
                    >
                </div>
                
                <div>
                    <label for="password">Heslo:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <p>Všechny položky jsou povinné</p>
                
                <p>Nemáte účet? <a href="register.php">Zaregistrujte se zde</a>.</p>
                <div>
                    <button type="submit">Přihlásit se</button>
                </div>
            </form>
        </div>
    <?php include 'footer.php'; ?>
</body>
</html>