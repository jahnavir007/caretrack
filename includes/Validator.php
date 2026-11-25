<?php
declare(strict_types=1);

/** Small server-side validation helper. Returns an array of field => error message. */
final class Validator
{
    public static function user(array $in, bool $requirePassword = true): array
    {
        $errors = [];
        if (mb_strlen(trim($in['name'] ?? '')) < 2) {
            $errors['name'] = 'Name must be at least 2 characters.';
        }
        if (!filter_var($in['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }
        if ($requirePassword && strlen($in['password'] ?? '') < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }
        if (!in_array($in['role'] ?? '', ['admin', 'staff', 'parent'], true)) {
            $errors['role'] = 'Choose a valid role.';
        }
        if (!empty($in['phone']) && !preg_match('/^\+?[0-9 ]{7,15}$/', $in['phone'])) {
            $errors['phone'] = 'Phone may contain digits, spaces and a leading +.';
        }
        return $errors;
    }

    public static function child(array $in): array
    {
        $errors = [];
        foreach (['first_name', 'last_name'] as $f) {
            if (mb_strlen(trim($in[$f] ?? '')) < 2) {
                $errors[$f] = 'Must be at least 2 characters.';
            }
        }
        $dob = $in['date_of_birth'] ?? '';
        $d   = DateTime::createFromFormat('Y-m-d', $dob);
        if (!$d || $d->format('Y-m-d') !== $dob) {
            $errors['date_of_birth'] = 'Date of birth must be YYYY-MM-DD.';
        } elseif ($d > new DateTime('today')) {
            $errors['date_of_birth'] = 'Date of birth cannot be in the future.';
        } elseif ($d < (new DateTime('today'))->modify('-18 years')) {
            $errors['date_of_birth'] = 'Child must be under 18.';
        }
        if (mb_strlen($in['allergies'] ?? '') > 255) {
            $errors['allergies'] = 'Keep allergies under 255 characters.';
        }
        return $errors;
    }

    public static function activity(array $in): array
    {
        $errors = [];
        if (!ctype_digit((string) ($in['child_id'] ?? ''))) {
            $errors['child_id'] = 'Select a child.';
        }
        if (!in_array($in['activity_type'] ?? '', ['meal', 'nap', 'learning', 'play', 'incident', 'medication', 'other'], true)) {
            $errors['activity_type'] = 'Choose a valid activity type.';
        }
        $len = mb_strlen(trim($in['description'] ?? ''));
        if ($len < 3 || $len > 500) {
            $errors['description'] = 'Description must be 3-500 characters.';
        }
        $ts = $in['occurred_at'] ?? '';
        if (!$ts || strtotime($ts) === false) {
            $errors['occurred_at'] = 'Enter a valid date and time.';
        } elseif (strtotime($ts) > time() + 300) {
            $errors['occurred_at'] = 'Activity time cannot be in the future.';
        }
        return $errors;
    }
}
