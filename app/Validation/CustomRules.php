<?php
namespace App\Validation;

class CustomRules
{
    /**
     * Custom validation function to check time format.
     *
     * @param string $str The field value.
     * @param string $params Any additional parameters.
     * @param array $data All data from the form submission.
     * @return bool
     */
    public function valid_time(string $str, string $params, array $data): bool
    {
        // Check if the time is in a valid format (e.g., HH:MM:SS)
        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $str) === 1;
    }
}
