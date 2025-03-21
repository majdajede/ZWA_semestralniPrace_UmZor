<?php
/**
 * This script handles the user profile page functionality.
 * 
 * It performs the following tasks:
 * - Starts a session and includes necessary functions.
 * - Checks if the user is logged in, otherwise redirects to the login page.
 * - Loads user data from a JSON file and verifies its existence and validity.
 * - Retrieves the logged-in user's data from the loaded user data.
 * - Handles user account deletion upon form submission:
 *   - Removes the user from the user data.
 *   - Deletes the user's posts and associated photos.
 *   - Updates the user data file.
 *   - Ends the session and redirects to the homepage.
 * - Displays the user's profile information and provides options to edit the profile, log out, or delete the account.
 * 
 * @package UserProfile
 */
session_start();
require_once 'funkce.php';

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

$username = $_SESSION['username'];
$user = null;
$userIndex = null;

foreach ($usersData as $idx => $userData) {
    if ($userData['username'] === $username) {
        $user = $userData;
        $userIndex = $idx;
        break;
    }
}

if ($user === null) {
    die("Chyba: Uživatel nebyl nalezen v databázi.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    if ($userIndex !== null && isset($usersData[$userIndex])) {
        array_splice($usersData, $userIndex, 1);

        $postsFile = 'prispevky.json';
        if (file_exists($postsFile)) {
            $posts = json_decode(file_get_contents($postsFile), true);
            if (is_array($posts)) {
                $newPosts = [];
                foreach ($posts as $onePost) {
                    if (isset($onePost['author']) && $onePost['author'] === $username) {
                        if (!empty($onePost['id'])) {
                            $pattern = 'photos/' . $onePost['id'] . '-*.*';
                            $matchedFiles = glob($pattern);
                            foreach ($matchedFiles as $file) {
                                @unlink($file);
                            }
                        }
                    } else {
                        $newPosts[] = $onePost;
                    }
                }
                file_put_contents($postsFile, json_encode($newPosts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        session_unset();
        session_destroy();
        header("Location: index.php");
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="css/favicon.ico" type="image/x-icon">
    <title>Profil</title>
</head>
<body>
    <?php include 'headerHtml.php'; ?>
    <div class="center">
            <h1>Profil uživatele</h1>
            <p class="thick">Uživatelské jméno: <?= escape($user['username']) ?></p>
            <p>Email: <?= escape($user['email']) ?></p>
            <p>Role: <?= escape($user['role']) ?></p>

            <div>
                <a class="button" href="changeProfil.php">Úprava profilu</a>
            </div>

            <form class="form-container" action="logout.php" method="post" style="margin-top: 1em;">
                <button type="submit">Odhlásit se</button>
            </form>

            <form class="form-container" action="profil.php" method="post" style="margin-top: 1em;">
                <button type="submit" name="delete_user">Smazat účet</button>
            </form>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
