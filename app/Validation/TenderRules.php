<?php
namespace App\Validation;

class TenderRules
{
    /**
     * Custom validation function to check the date range.
     */
    public function check_open_period(string $str, string $fields, array $data): bool
    {
        // Check if opening_date and closing_date are provided and valid
        if (isset($data['opening_date']) && isset($data['closing_date'])) {
            return strtotime($data['opening_date']) <= strtotime($data['closing_date']);
        }
        return true; // Return true if one of the fields is missing
    }
}
