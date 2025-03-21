<?php
/**
 * This script handles user logout functionality.
 * 
 * It starts the session, includes necessary functions, and checks if the request method is POST.
 * If the request method is POST, it unsets and destroys the session, effectively logging the user out.
 * After logging out, it redirects the user to the index.php page.
 * If the request method is not POST, it simply redirects the user to the index.php page.
 * 
 */
session_start();
require_once 'funkce.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_unset();
    session_destroy();

    header("Location: index.php");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>
