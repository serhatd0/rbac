<?php

declare(strict_types=1);

namespace App\Http;

class ViewController
{
    public function login(): void
    {
        Response::view('login');
    }

    public function register(): void
    {
        Response::view('register');
    }

    public function dashboard(): void
    {
        Response::view('dashboard');
    }
    
    public function home(): void 
    {
        // Redirect home to login
        header('Location: /login');
        exit;
    }
}
