<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidRecurrence implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!is_array($value)) {
            return false;
        }

        $validFrequencies = ['daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'custom'];
        
        if (!in_array($value['frequency'] ?? null, $validFrequencies)) {
            return false;
        }

        // Validate days for weekly frequency
        if ($value['frequency'] === 'weekly') {
            if (empty($value['days']) || !is_array($value['days'])) {
                return false;
            }
            
            // Validate each day is between 0-6 (Sunday to Saturday)
            foreach ($value['days'] as $day) {
                if (!is_numeric($day) || $day < 0 || $day > 6) {
                    return false;
                }
            }
        }

        // Validate end_type if present
        $validEndTypes = ['never', 'after_occurrences', 'end_date'];
        if (isset($value['end_type']) && !in_array($value['end_type'], $validEndTypes)) {
            return false;
        }

        // Validate end_value based on end_type
        if (isset($value['end_type'])) {
            if ($value['end_type'] === 'after_occurrences' && (!isset($value['end_value']) || !is_numeric($value['end_value']) || $value['end_value'] <= 0)) {
                return false;
            }
            
            if ($value['end_type'] === 'end_date' && (!isset($value['end_value']) || !strtotime($value['end_value']))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The recurrence configuration is invalid.';
    }
}
