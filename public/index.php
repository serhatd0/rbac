<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Auth\AuthController;
use App\Auth\AuthService;
use App\Auth\Jwt;
use App\Auth\RefreshTokenService;
use App\Config\Env;
use App\Database\Connection;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\RateLimitMiddleware;
use App\Http\Request;
use App\Http\Router;
use App\Rbac\AdminController;
use App\Rbac\RbacRepository;
use App\User\UserController;
use App\User\UserRepository;

// Load Env
Env::load(__DIR__ . '/../.env');

// Handle CORS
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
}

// Errors
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    // Dependencies
    $db = Connection::get();
    $jwt = new Jwt();
    $userRepo = new UserRepository($db);
    $rbacRepo = new RbacRepository($db);
    $refreshService = new RefreshTokenService($db);
    $authService = new AuthService($userRepo, $refreshService, $jwt);

    // Controllers
    $authController = new AuthController($authService);
    $userController = new UserController($userRepo);
    $adminController = new AdminController($db, $userRepo);

    // Middlewares
    $authMiddleware = new AuthMiddleware($jwt);
    $permissionMiddleware = new PermissionMiddleware($rbacRepo);
    $rateLimitMiddleware = new RateLimitMiddleware($db);

    // Router
    $router = new Router();

    // UI Routes
    $viewController = new \App\Http\ViewController();
    $router->get('/', [$viewController, 'home']);
    $router->get('/login', [$viewController, 'login']);
    $router->get('/register', [$viewController, 'register']);
    $router->get('/dashboard', [$viewController, 'dashboard']);

    // Public Routes
    $router->post('/auth/register', [$authController, 'register']);
    $router->post('/auth/login', function(Request $req) use ($authController, $rateLimitMiddleware) {
        $rateLimitMiddleware->handle($req);
        $authController->login($req);
    });
    $router->post('/auth/refresh', [$authController, 'refresh']);
    $router->post('/auth/logout', [$authController, 'logout']);

    // Protected Routes
    $router->get('/me', function(Request $req) use ($userController, $authMiddleware) {
        $authMiddleware->handle($req);
        $userController->me($req);
    });

    // Admin Routes
    $router->get('/admin/users', function(Request $req) use ($adminController, $authMiddleware, $permissionMiddleware) {
        $authMiddleware->handle($req);
        $permissionMiddleware->handle($req, 'users.read');
        $adminController->index($req);
    });

    $router->post('/admin/users/{id}/roles', function(Request $req, $id) use ($adminController, $authMiddleware, $permissionMiddleware) {
        $authMiddleware->handle($req);
        $permissionMiddleware->handle($req, 'users.roles.write');
        $adminController->assignRole($req, $id);
    });

    // Dispatch
    $request = new Request();
    $router->dispatch($request);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error', 'details' => $e->getMessage()]);
}
