<?php
/**
 * This script handles user management functionalities for an admin user.
 * It allows the admin to update user roles and delete users along with their posts.
 * 
 * Functionalities:
 * - Checks if the user is an admin, otherwise redirects to the index page.
 * - Handles POST requests to update user roles or delete users.
 * - Validates CSRF tokens for security.
 * - Updates user roles and saves the changes.
 * - Deletes users and their associated posts, and removes related files.
 * - Displays success or error messages based on the operations performed.
 * 
 * Dependencies:
 * - Requires 'funkce.php' for utility functions.
 * - Requires 'config.php' for loading and saving user data.
 * - Requires 'headerHtml.php' and 'footer.php' for HTML layout.
 * - Requires 'css/style.css' for styling.
 * 
 * HTML Output:
 * - Displays a table of users with options to update roles or delete users.
 * - Shows success or error messages based on the operations performed.
 * 
 * Security:
 * - Validates CSRF tokens to prevent CSRF attacks.
 * - Ensures only admin users can access this page.
 * 
 * @package UserManagement
 */
session_start();
require_once 'funkce.php';

$config = require 'config.php';
$users = $config['load_users']();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }

    if (isset($_POST['update_role'])) {
        $user_id  = (int)$_POST['user_id'];
        $new_role = ($_POST['role'] === 'admin') ? 'admin' : 'user';

        if (isset($users[$user_id])) {
            try {
                $users[$user_id]['role'] = $new_role;

                if ($users[$user_id]['username'] === $_SESSION['username'] && $new_role === 'user') {
                    $_SESSION['role'] = 'user';
                }

                $config['save_users']($users);
                $_SESSION['message'] = 'Role byla úspěšně změněna.';
            } catch (Exception $e) {
                $_SESSION['error'] = 'Chyba při aktualizaci role: ' . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = 'Uživatel nebyl nalezen.';
        }

        header("Location: vypisUzivatelu.php");
        exit();
    }

    if (isset($_POST['delete_user'])) {
        $user_id = (int)$_POST['user_id'];

        if (isset($users[$user_id])) {
            $deletedUsername = $users[$user_id]['username'];

            array_splice($users, $user_id, 1);

            $postsFile = 'prispevky.json';
            if (file_exists($postsFile)) {
                $posts = json_decode(file_get_contents($postsFile), true);
                if (is_array($posts)) {
                    $newPosts = [];
                    foreach ($posts as $p) {
                        if (isset($p['author']) && $p['author'] === $deletedUsername) {
                            if (!empty($p['id'])) {
                                $pattern = 'photos/' . $p['id'] . '-*.*';
                                $matchedFiles = glob($pattern);
                                foreach ($matchedFiles as $file) {
                                    @unlink($file);
                                }
                            }
                        } else {
                            $newPosts[] = $p;
                        }
                    }
                    file_put_contents($postsFile, json_encode($newPosts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
            }

            $config['save_users']($users);

            if ($deletedUsername === $_SESSION['username']) {
                session_unset();
                session_destroy();
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['message'] = 'Uživatel (včetně jeho příspěvků) byl úspěšně smazán.';
            }
        } else {
            $_SESSION['error'] = 'Uživatel nebyl nalezen.';
        }
        header("Location: vypisUzivatelu.php");
        exit();
    }
}

$message = $_SESSION['message'] ?? '';
$error   = $_SESSION['error']   ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Správa uživatelů</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="css/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include "headerHtml.php"; ?>
    <div class="center">
        <h1 class="hide">Správa uživatelů</h1>

        <?php if (!empty($message)): ?>
            <p class="success"><?= escape($message) ?></p>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <p class="error"><?= escape($error) ?></p>
        <?php endif; ?>

        <table class="user-management-table">
            <thead>
                <tr class="firstRow">
                    <th>ID</th>
                    <th>Uživatelské jméno</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($id = 1; $id < count($users); $id++): ?>
                    <tr>
                        <td><?= escape($id) ?></td>
                        <td><?= escape($users[$id]['username']) ?></td>
                        <td><?= escape($users[$id]['email']) ?></td>
                        <td><?= escape($users[$id]['role']) ?></td>
                        <td>
                            <form class="form-inline" method="POST" action="vypisUzivatelu.php">
                                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                                <input type="hidden" name="user_id" value="<?= escape($id) ?>">

                                <select name="role">
                                    <option value="user"  <?= ($users[$id]['role'] === 'user')  ? 'selected' : '' ?>>User</option>
                                    <option value="admin" <?= ($users[$id]['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                                </select>

                                <button type="submit" name="update_role">Upravit roli</button>
                            </form>
                            
                            <form class="form-inline" method="POST" action="vypisUzivatelu.php" style="margin-top:4px;">
                                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                                <input type="hidden" name="user_id" value="<?= escape($id) ?>">

                                <button type="submit" name="delete_user">
                                    Smazat uživatele
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
