<?php
use CodeIgniter\CodeIgniter;

if (!function_exists('user_timezone')) {
    /**
     * Retrieve the user's timezone from the database.
     *
     * @param int $userId The user's ID.
     * @return string|null The timezone string or null if not found.
     */
    function user_timezone($userId = null): string
    {
        // Load the UserSettingsModel
        $userSettingsModel = model('App\Models\UserSettingModel');

        // Retrieve user settings based on user ID
        $settings = $userSettingsModel->where(['user_id' => $userId, 'setting_name' => USERSETTING_TIMEZONE])->first();

        $config = config('App');
        // Return the timezone or null if not set
        return $settings['timezone'] ?? $config->appTimezone;
    }
}

if (!function_exists('convert_to_user_timezone')) {
    /**
     * Convert a given datetime to the user's timezone.
     *
     * @param string $datetime The datetime string to convert.
     * @param string $timezone The user's timezone.
     * @return string The converted datetime string.
     */
    function convert_to_user_timezone($datetime, $timezone)
    {
        $date = new \DateTime($datetime, new \DateTimeZone('UTC'));
        $date->setTimezone(new \DateTimeZone($timezone));
        return $date->format('Y-m-d H:i:s');
    }
}