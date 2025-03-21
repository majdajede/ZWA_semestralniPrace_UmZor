<?php
/**
 * Configuration file for user data management.
 *
 * This configuration file contains settings and functions for loading and saving user data
 * from and to a JSON file named 'users.json'. The file is expected to be located in the same
 * directory as this script.
 *
 * @property string $users_file The path to the 'users.json' file.
 * @property callable $load_users A function to load user data from the 'users.json' file.
 * @property callable $save_users A function to save user data to the 'users.json' file.
 */
return [

    'users_file' => __DIR__ . '/users.json',

    /**
     * Loads user data from a JSON file.
     *
     * This function reads the contents of the 'users.json' file located in the same directory
     * as the script, decodes the JSON data, and returns it as an associative array.
     *
     * @return array The decoded user data.
     * @throws RuntimeException If the 'users.json' file does not exist or if there is an error decoding the JSON data.
     */
    'load_users' => function() {
        $filePath = __DIR__ . '/users.json';
        
        if (!file_exists($filePath)) {
            die('Databázový soubor users.json neexistuje.');
        }

        $jsonData = file_get_contents($filePath);
        $users = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            die('Chyba při dekódování JSON dat: ' . json_last_error_msg());
        }

        return $users;
    },

    /**
     * Saves the given users array to a JSON file.
     *
     * This function takes an array of users, encodes it into a JSON format,
     * and saves it to a file named 'users.json' located in the same directory
     * as this script. If there is an error during the JSON encoding or file
     * saving process, the function will terminate the script and display an
     * error message.
     *
     * @param array $users The array of users to be saved.
     *
     * @throws RuntimeException If there is an error encoding the JSON data.
     * @throws RuntimeException If there is an error saving the JSON data to the file.
     */
    'save_users' => function($users) {
        $filePath = __DIR__ . '/users.json';
        
        $jsonData = json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($jsonData === false) {
            die('Chyba při kódování JSON dat: ' . json_last_error_msg());
        }

        if (file_put_contents($filePath, $jsonData) === false) {
            die('Chyba při ukládání do souboru users.json.');
        }
    }
];
?>