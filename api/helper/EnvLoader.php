<?php

class EnvLoader {
    public static function load($path) {
        if (!file_exists($path)) {
            throw new Exception("Missing .env file at $path");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Ignore comments
            if (strpos(trim($line), '#') === 0) continue;

            // Parse KEY=VALUE
            [$name, $value] = array_map('trim', explode('=', $line, 2));
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }

    public static function get($key, $default = null) {
        return $_ENV[$key] ?? getenv($key) ?? $default;
    }
}
