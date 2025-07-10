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

    // Add other general functions here as needed
    ?>
    