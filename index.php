<?php
/**
 * This script is the main entry point for the UmZor application.
 * It handles session management, loads posts from a JSON file, sorts them by date,
 * and displays them with pagination. It also includes the header and footer HTML.
 *
 * Variables:
 * - $role: The role of the current user, defaulting to 'visitor'.
 * - $postsFile: The path to the JSON file containing the posts.
 * - $photosDir: The directory where post photos are stored.
 * - $postsPerPage: The number of posts to display per page.
 * - $posts: The array of posts loaded from the JSON file.
 * - $currentPage: The current page number for pagination.
 * - $totalPages: The total number of pages based on the number of posts.
 * - $startIndex: The starting index for slicing the posts array.
 * - $currentPosts: The array of posts to display on the current page.
 *
 * HTML Structure:
 * - Includes headerHtml.php for the header section.
 * - Displays a welcome message and description.
 * - Iterates over $currentPosts to display each post with its details and images.
 * - Provides edit links for posts if the user is an admin or the author.
 * - Displays pagination links for navigating between pages.
 * - Includes footer.php for the footer section.
 */
session_start();
require_once 'funkce.php';

$role       = isset($_SESSION['role']) ? $_SESSION['role'] : 'visitor';
$postsFile  = 'prispevky.json';
$photosDir  = 'photos';
$postsPerPage = 4;

$posts = file_exists($postsFile) ? json_decode(file_get_contents($postsFile), true) : [];

usort($posts, function ($a, $b) {
    return strtotime($b['timePost']) - strtotime($a['timePost']);
});

$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$totalPages  = ceil(count($posts) / $postsPerPage);

$startIndex   = ($currentPage - 1) * $postsPerPage;
$currentPosts = array_slice($posts, $startIndex, $postsPerPage);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/index.js" defer></script>
    <link rel="icon" href="css/favicon.ico" type="image/x-icon">
    <title>UmZor</title>
</head>
<body>
    <?php include 'headerHtml.php'; ?>
        <div class="center">
            <h1 class="hide">Vítejte na UmZor!</h1>
            <p class="hide">UmZor podporuje mladé umělce a pomáhá jim se rozvíjet, inspirovat a sdílet svá umění</p>

            <div class="posts">
                <?php if (!empty($currentPosts)): ?>
                    <?php foreach ($currentPosts as $post): ?>
                        <div class="post">
                            <h2><?= escape($post['artname']) ?></h2>
                            <p><span class="thick">Autor:</span> <?= escape($post['author']) ?></p>
                            <p><span class="thick">Technika:</span> <?= escape($post['technique']) ?></p>
                            <p>Datum: <?= escape($post['timePost']) ?></p>

                            <?php
                            $postId = $post['id'] ?? '';
                            $matchedFiles = glob($photosDir . '/' . $postId . '-*.*');
                            ?>

                            <?php if (!empty($matchedFiles)): ?>
                                <?php if (count($matchedFiles) === 1): ?>
                                    <img src="<?= $photosDir . '/' . escape(basename($matchedFiles[0])) ?>" alt="<?= escape($post['artname']) ?>">
                                <?php else: ?>
                                    <div 
                                        class="slider-container"
                                        data-photosdir="<?= escape($photosDir) ?>"
                                        data-photos="<?= htmlspecialchars(json_encode(array_map('basename', $matchedFiles)), ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                        <img src="<?= $photosDir . '/' . escape(basename($matchedFiles[0])) ?>" alt="<?= escape($post['artname']) ?>">
                                        <button class="slider-arrow left">&#10094;</button>
                                        <button class="slider-arrow right">&#10095;</button>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['username']) && ($role === 'admin' || $_SESSION['username'] === $post['author'])): ?>
                                <a href="changePrispevek.php?id=<?= urlencode($post['id']) ?>">Upravit</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Žádné příspěvky k zobrazení.</p>
                <?php endif; ?>
            </div>

            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="index.php?page=1">1</a>
                <?php endif; ?>

                <?php if ($currentPage > 2): ?>
                    <a href="index.php?page=<?= $currentPage - 1 ?>"><?= $currentPage - 1 ?></a>
                <?php endif; ?>

                <a href="index.php?page=<?= $currentPage ?>" class="current"><?= $currentPage ?></a>

                <?php if ($currentPage < $totalPages - 1): ?>
                    <a href="index.php?page=<?= $currentPage + 1 ?>"><?= $currentPage + 1 ?></a>
                <?php endif; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="index.php?page=<?= $totalPages ?>"><?= $totalPages ?></a>
                <?php endif; ?>
            </div>

        </div>

    <?php include 'footer.php'; ?>
</body>
</html>
