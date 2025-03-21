<?php
/**
 * This script checks the availability of a username or email in the users.json file.
 * It returns a JSON response indicating whether the value is available or not.
 *
 * @file /home/lebedmag/www/semestralka/checkAvailability.php
 * @header Content-Type: application/json
 * @session_start Starts a new session or resumes an existing session
 *
 * @param string $_GET['field'] The field to check ('username' or 'email')
 * @param string $_GET['value'] The value to check for availability
 *
 * @return json {
 *     "valid": boolean, // true if the value is available, false otherwise
 *     "message": string // optional message if the value is not available
 * }
 */
header('Content-Type: application/json');
session_start();

$usersFile = 'users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

$field = $_GET['field'] ?? '';
$value = trim($_GET['value'] ?? '');

$response = ['valid' => true];

foreach ($users as $user) {
    if ($field === 'username' && $user['username'] === $value) {
        $response = ['valid' => false, 'message' => 'Uživatelské jméno je již obsazeno.'];
        break;
    }
    if ($field === 'email' && $user['email'] === $value) {
        $response = ['valid' => false, 'message' => 'Email je již použit.'];
        break;
    }
}

echo json_encode($response);
exit();
