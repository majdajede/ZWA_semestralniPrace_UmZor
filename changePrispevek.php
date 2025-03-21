<?php
/**
 * This script handles the updating and deletion of posts.
 * 
 * It performs the following tasks:
 * - Starts a session and includes necessary functions.
 * - Checks if the user is logged in and has the appropriate role (user or admin).
 * - Loads the posts from a JSON file.
 * - Validates the post ID from the GET request.
 * - Checks if the user has permission to edit the post.
 * - Handles form submission for updating or deleting the post.
 * - Validates the CSRF token.
 * - Validates the form fields and updates the post if valid.
 * - Deletes the post and associated files if requested.
 * - Displays the form for editing the post.
 * 
 * @package ChangePrispevek
 */


session_start();
require_once 'funkce.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['user', 'admin'])) {
    header("Location: login.php");
    exit();
}

$postsFile = 'prispevky.json';
$posts = file_exists($postsFile) ? json_decode(file_get_contents($postsFile), true) : [];
$errors = [];
$fieldErrors = [
    'artname' => '',
    'technique' => ''
];

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Neplatné ID příspěvku.");
}

$index = null;
$post = null;
foreach ($posts as $i => $p) {
    if (isset($p['id']) && $p['id'] === $id) {
        $index = $i;
        $post = $p;
        break;
    }
}
if ($post === null) {
    die("Příspěvek s tímto ID neexistuje.");
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['username'] !== $post['author']) {
    die("Nemáte oprávnění upravovat tento příspěvek.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        array_splice($posts, $index, 1);

        $pattern = 'photos/' . $id . '-*.*';
        $matchedFiles = glob($pattern);
        foreach ($matchedFiles as $file) {
            @unlink($file);
        }
        file_put_contents($postsFile, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header("Location: index.php");
        exit();
    } elseif ($action === 'update') {
        $artname   = trim($_POST['artname'] ?? '');
        $technique = trim($_POST['technique'] ?? $post['technique']);

        if (strlen($artname) < 3) {
            $fieldErrors['artname'] = "Název příspěvku musí mít aspoň 3 znaky.";
        }
        if (strlen($artname) >=16) {
            $fieldErrors['artname'] = "Název díla nesmí přesáhnout 15 znaků.";
        }
        $allowedTechniques = ['Pastel', 'Tužka', 'Olej', 'Akryl', 'Akvarel', 'Keramika', 'Fotka'];
        if (!empty($technique) && !in_array($technique, $allowedTechniques)) {
            $fieldErrors['technique'] = "Neplatná technika.";
        }

        if (empty($fieldErrors['artname']) && empty($fieldErrors['technique'])) {
            $posts[$index]['artname']   = $artname;
            $posts[$index]['technique'] = $technique;

            file_put_contents($postsFile, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            header("Location: index.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Upravit příspěvek</title>
    <script src="js/changePrispevek.js" defer></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="css/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'headerHtml.php'; ?>
        <div class="center">
            <h1>Upravit příspěvek</h1>

            <form class="form-container" id="changePrispevekForm" action="changePrispevek.php?id=<?= urlencode($id) ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

                <label for="artname">Název příspěvku:</label>
                <input 
                    type="text" 
                    id="artname" 
                    name="artname"
                    maxlength="15"
                    value="<?= escape($post['artname']) ?>"
                    required
                >
                <?php if (!empty($fieldErrors['artname'])): ?>
                    <p class="error"><?= escape($fieldErrors['artname']) ?></p>
                <?php endif; ?>

                <label for="technique">Technika:</label>
                <select name="technique" id="technique">
                    <?php foreach (['Pastel', 'Tužka', 'Olej', 'Akryl', 'Akvarel', 'Keramika', "Fotka"] as $option): ?>
                        <option value="<?= $option ?>" <?= $post['technique'] === $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($fieldErrors['technique'])): ?>
                    <p class="error"><?= escape($fieldErrors['technique']) ?></p>
                <?php endif; ?>
                    <p>Vyplňte prosím všechna pole</p>
                <button type="submit" name="action" value="update">Uložit</button>
                <button type="submit" id="deletePostButton" name="action" value="delete">Smazat</button>
            </form>
        </div>
    <?php include 'footer.php'; ?>
</body>
</html>


