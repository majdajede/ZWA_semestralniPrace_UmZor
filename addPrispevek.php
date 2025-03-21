<?php
/**
 * This script handles the addition of a new post by a user. It performs the following tasks:
 * 
 * - Starts a session and includes necessary configuration and function files.
 * - Checks if the user is logged in and has the appropriate role ('user' or 'admin').
 * - Initializes an array to store field errors.
 * - Defines the file path for storing posts and the directory for storing photos.
 * - If the request method is POST, it validates the CSRF token.
 * - Retrieves and sanitizes form input data (art name, technique, and photos).
 * - Validates the input data:
 *   - Ensures the art name is not empty, is between 3 and 15 characters long.
 *   - Ensures the technique is one of the allowed techniques.
 *   - Ensures at least one photo is uploaded.
 * - Generates a unique ID for the new post.
 * - If there are no validation errors, processes the uploaded photos.
 * - If photo processing is successful, creates a new post array and appends it to the existing posts.
 * - Saves the updated posts array to a JSON file.
 * - Sets a success message in the session and redirects the user to the index page.
 * 
 * The HTML part of the script includes a form for adding a new post, with fields for art name, technique, and photos.
 * It also displays any validation errors and includes header and footer HTML files.
 */
session_start();
require 'configPhotos.php';
require_once 'funkce.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['user', 'admin'], true)) {
    header("Location: login.php");
    exit();
}

$fieldErrors = [
    'artname'   => '',
    'technique' => '',
    'photo'     => ''
];

$postsFile = 'prispevky.json';
$photosDir = 'photos';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }

    $author    = $_SESSION['username'] ?? null;
    $artname   = trim($_POST['artname'] ?? '');
    $technique = trim($_POST['technique'] ?? '');
    $timePost  = date('H:i d.m.Y', strtotime('+1 hours'));
    $photos    = $_FILES['photo'] ?? null;

    $allowedTechniques = ["Pastel", "Tužka", "Olej", "Akryl", "Akvarel", "Keramika", "Fotka"];

    if ($artname === '') {
        $fieldErrors['artname'] = "Název díla nesmí být prázdný.";
    } elseif (strlen($artname) >= 16) {
        $fieldErrors['artname'] = "Název díla nesmí přesáhnout 15 znaků.";
    } elseif (strlen($artname) < 3) {
        $fieldErrors['artname'] = "Název díla musí mít minimálně 3 znaky.";
    }

    if (!in_array($technique, $allowedTechniques, true)) {
        $fieldErrors['technique'] = "Musíte zvolit uměleckou techniku.";
    }

    if (!$photos || empty($photos['name'][0])) {
        $fieldErrors['photo'] = "Musíte nahrát alespoň jednu fotku.";
    }

    $newId = bin2hex(random_bytes(8));

    if (
        empty($fieldErrors['artname']) &&
        empty($fieldErrors['technique']) &&
        empty($fieldErrors['photo'])
    ) {
        $savedPhotos = processPhotos($photos, $errorArr, $photosDir, $newId);
        if (!empty($errorArr)) {
            $fieldErrors['photo'] = implode(' ', $errorArr);
        }
    }

    if (
        empty($fieldErrors['artname']) &&
        empty($fieldErrors['technique']) &&
        empty($fieldErrors['photo'])
    ) {
        $newPost = [
            'id'        => $newId,
            'author'    => $author,
            'artname'   => $artname,
            'technique' => $technique,
            'timePost'  => $timePost
        ];

        $posts = file_exists($postsFile) ? json_decode(file_get_contents($postsFile), true) : [];
        $posts[] = $newPost;

        if (file_put_contents($postsFile, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            $_SESSION['success_message'] = "Příspěvek byl úspěšně nahrán.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přidat příspěvek</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/addPrispevek.js" defer></script>
    <link rel="icon" href="css/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'headerHtml.php'; ?>
    <div class="center">
        <h1>Přidat příspěvek</h1>

        <form class="form-container" action="addPrispevek.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

            <label for="artname">Název díla:</label>
            <input 
                type="text" 
                id="artname" 
                name="artname" 
                maxlength="15" 
                required
                value="<?= isset($_POST['artname']) ? escape($_POST['artname']) : '' ?>"
            >
            <?php if (!empty($fieldErrors['artname'])): ?>
                <p class="error"><?= escape($fieldErrors['artname']) ?></p>
            <?php endif; ?>

            <label for="technique">Technika:</label>
            <select id="technique" name="technique" required>
                <option value="">-- Vyberte --</option>
                <option value="Pastel"   <?= (($_POST['technique']??'') === 'Pastel')   ? 'selected' : '' ?>>Pastel</option>
                <option value="Tužka"    <?= (($_POST['technique']??'') === 'Tužka')    ? 'selected' : '' ?>>Tužka</option>
                <option value="Olej"     <?= (($_POST['technique']??'') === 'Olej')     ? 'selected' : '' ?>>Olej</option>
                <option value="Akryl"    <?= (($_POST['technique']??'') === 'Akryl')    ? 'selected' : '' ?>>Akryl</option>
                <option value="Akvarel"  <?= (($_POST['technique']??'') === 'Akvarel')  ? 'selected' : '' ?>>Akvarel</option>
                <option value="Keramika" <?= (($_POST['technique']??'') === 'Keramika') ? 'selected' : '' ?>>Keramika</option>
                <option value="Fotka"    <?= (($_POST['technique']??'') === 'Fotka')    ? 'selected' : '' ?>>Fotka</option>
            </select>
            <?php if (!empty($fieldErrors['technique'])): ?>
                <p class="error"><?= escape($fieldErrors['technique']) ?></p>
            <?php endif; ?>

            <label for="photo">Fotky (max 4):</label>
            <input 
                type="file" 
                id="photo" 
                name="photo[]" 
                multiple 
                required
            >
            <?php if (!empty($fieldErrors['photo'])): ?>
                <p class="error"><?= escape($fieldErrors['photo']) ?></p>
            <?php endif; ?>

            <p>Vyplňte prosím všechna pole</p>

            <button type="submit">Přidat příspěvek</button>
        </form>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
