<?php

declare(strict_types=1);

namespace App\Http;

class Response
{
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function error(string $message, int $status = 400): void
    {
        self::json(['error' => $message], $status);
    }

    public static function view(string $path, array $data = []): void
    {
        extract($data);
        $fullPath = __DIR__ . '/../Views/' . $path . '.php';
        if (!file_exists($fullPath)) {
            http_response_code(404);
            echo "View not found: $path";
            exit;
        }
        require $fullPath;
        exit;
    }
}
