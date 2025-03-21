<?php
/**
 * This script is responsible for generating the navigation menu based on the user's session role.
 * 
 * It performs the following tasks:
 * - Starts a session if it hasn't been started already.
 * - Retrieves the user's role and username from the session, defaulting to 'visitor' and 'Host' respectively if not set.
 * - Generates a navigation menu with different options based on the user's role:
 *   - If the user is a 'visitor', they are shown links to 'Domů', 'Nahrát' (login page), and 'Profil' (login page).
 *   - If the user is logged in (any role other than 'visitor'), they are shown links to 'Domů', 'Nahrát' (addPrispevek.php), and 'Profil' (profil.php).
 *   - If the user is an 'admin', they are additionally shown a link to 'Výpis uživatelů' (vypisUzivatelu.php).
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'visitor';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Host';
?>
    <nav>
        <ul>
            <li><a href="index.php">Domů</a></li>
            <?php if ($role === 'visitor'): ?>
                <li><a href="login.php">Nahrát</a></li>
            <?php else: ?>
                <li><a href="addPrispevek.php">Nahrát</a></li>
            <?php endif; ?>

            <?php if ($role === 'admin'): ?>
                <li><a href="vypisUzivatelu.php">Výpis uživatelů</a></li>
            <?php endif; ?>

            <?php if ($role === 'visitor'): ?>
                <li><a href="login.php">Profil</a></li>
            <?php else: ?>
                <li><a href="profil.php">Profil</a></li>
            <?php endif; ?>
        </ul>
    </nav>
