<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

class RsqlFilter
{
    // Allowed columns to avoid SQL injection
    public static $allowed = [
        'name', 'email', 'verified', 'created_at', 'last_login_at'
    ];

    public static function apply(Builder $q, string $filter)
    {
        $conditions = explode(';', $filter);

        foreach ($conditions as $expr) {
            $expr = trim($expr);
            if (! $expr) continue;

            // Supported operators
            if (strpos($expr, '==') !== false) {
                [$field, $value] = explode('==', $expr, 2);

                $field = trim($field);

                if (! in_array($field, self::$allowed)) continue;

                // Wildcard search
                if (str_contains($value, '*')) {
                    $value = str_replace('*', '%', $value);
                    $q->where($field, 'LIKE', $value);
                } else {
                    $q->where($field, '=', $value);
                }

            } elseif (preg_match('/(.+)(>=|<=|>|<)(.+)/', $expr, $m)) {
                $field = trim($m[1]);
                $op    = trim($m[2]);
                $value = trim($m[3]);

                if (! in_array($field, self::$allowed)) continue;

                $q->where($field, $op, $value);
            }
        }

        return $q;
    }
}
