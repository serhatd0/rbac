<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Database\Connection;
use App\Http\Request;
use App\Http\Response;
use PDO;

class RateLimitMiddleware
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function handle(Request $request): void
    {
        $ip = $request->getIp();
        
        // Simple file based for MVP as per prompt "DB tablosu veya file-based"
        // Let's use file for simplicity and speed in this MVP without creating new table if table migration is hard.
        // But users table schema is already set. Let's make a quick file based 
        // rate limiter in temp dir.
        
        $file = sys_get_temp_dir() . '/rate_limit_' . md5($ip);
        $limit = 5; // requests
        $window = 60; // seconds

        $data = file_exists($file) ? json_decode(file_get_contents($file), true) : ['count' => 0, 'start_time' => time()];
        
        if (time() - $data['start_time'] > $window) {
            $data = ['count' => 1, 'start_time' => time()];
        } else {
            $data['count']++;
        }

        if ($data['count'] > $limit) {
            Response::error('Too Many Requests', 429);
        }

        file_put_contents($file, json_encode($data));
    }
}
