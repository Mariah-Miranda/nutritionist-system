<?php
// includes/functions.php

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Calculates the current age based on a date of birth.
 * @param string $dob_string Date of birth in 'YYYY-MM-DD' format.
 * @return int|null The calculated age, or null if DOB is invalid.
 */
function calculateAgeFromDob($dob_string) {
    if (empty($dob_string) || !strtotime($dob_string)) {
        return null;
    }
    $dob = new DateTime($dob_string);
    $now = new DateTime();
    $interval = $now->diff($dob);
    return $interval->y;
}

/**
 * Basic validation for a date string.
 * @param string $dateString The date string to validate.
 * @return bool True if the date string is valid, false otherwise.
 */
function isValidDate($dateString) {
    return (bool)strtotime($dateString);
}

/**
 * Basic validation for a time string.
 * @param string $timeString The time string to validate.
 * @return bool True if the time string is valid, false otherwise.
 */
function isValidTime($timeString) {
    // Prepends a dummy date to ensure valid parsing even for time-only strings
    return (bool)strtotime("1970-01-01 " . $timeString);
}

// Add other general functions here as needed
?>