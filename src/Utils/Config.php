<?php
/**
 * Configuration Manager & Environment Loader
 * Patient Queue Management System (PQS)
 */

declare(strict_types=1);

namespace PQS\Utils;

class Config
{
    private static array $config = [];
    private static bool $loaded = false;

    /**
     * Load environment configuration from .env file
     */
    public static function load(string $baseDir): void
    {
        if (self::$loaded) {
            return;
        }

        $envFile = rtrim($baseDir, '/\\') . DIRECTORY_SEPARATOR . '.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }

                if (str_contains($line, '=')) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);

                    // Strip surrounding quotes
                    if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                        (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                        $value = substr($value, 1, -1);
                    }

                    self::$config[$key] = $value;
                    if (!isset($_ENV[$key])) {
                        $_ENV[$key] = $value;
                    }
                }
            }
        }

        self::$loaded = true;
    }

    /**
     * Get a configuration value with an optional default
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$config)) {
            return self::$config[$key];
        }

        $envVal = getenv($key);
        if ($envVal !== false) {
            return $envVal;
        }

        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        return $default;
    }

    /**
     * Get as integer
     */
    public static function getInt(string $key, int $default = 0): int
    {
        return (int) self::get($key, $default);
    }

    /**
     * Get as boolean
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $val = strtolower((string) self::get($key, $default ? 'true' : 'false'));
        return in_array($val, ['1', 'true', 'on', 'yes'], true);
    }
}

