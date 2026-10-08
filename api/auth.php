<?php
// api/auth.php - Permissive Authentication Controller (Accepts Any Credentials)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/db.php';

$action = $_GET['action'] ?? '';

// 1. Current Session Verification
if ($action === 'current_user') {
    if (!empty($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
        echo json_encode([
            'authenticated' => true,
            'user' => $_SESSION['user']
        ]);
    } else {
        echo json_encode([
            'authenticated' => false
        ]);
    }
    exit;
}

// 2. Permissive Login (Accepts any email and password)
if ($action === 'login') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = strtolower(trim($input['email'] ?? ''));
    $pass  = $input['password'] ?? '';

    // Fallback defaults if fields are submitted blank
    if (empty($email)) {
        $email = 'user@karyaflow.internal';
    }
    if (empty($pass)) {
        $pass = 'password';
    }

    try {
        // Check if user exists in MySQL
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Existing user: accept login immediately regardless of password
            $_SESSION['user'] = [
                'id'    => $user['id'],
                'name'  => $user['name'] ?? 'Stakeholder',
                'email' => $user['email'],
                'role'  => $user['role'] ?? 'FINANCE_OPERATOR'
            ];
        } else {
            // New user: auto-create account on the fly and log in
            $derivedName = ucwords(str_replace(['.', '_', '-'], ' ', explode('@', $email)[0]));
            if (empty($derivedName)) {
                $derivedName = 'Stakeholder';
            }
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $role = 'FINANCE_OPERATOR';

            $ins = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
            $ins->execute([$derivedName, $email, $hash, $role]);
            $newId = $pdo->lastInsertId();

            $_SESSION['user'] = [
                'id'    => $newId,
                'name'  => $derivedName,
                'email' => $email,
                'role'  => $role
            ];
        }

        // Stamp audit log
        try {
            $pdo->prepare("INSERT INTO audit_logs (workflow_id, action, details) VALUES (?, ?, ?)")
                ->execute([1, 'AUTH_LOGIN_SUCCESS', "Session established for: {$email}"]);
        } catch (Exception $e) {}

        echo json_encode([
            'success' => true,
            'user' => $_SESSION['user']
        ]);
    } catch (Exception $e) {
        // In-memory fallback if database table is unavailable
        $_SESSION['user'] = [
            'id'    => 1,
            'name'  => ucwords(explode('@', $email)[0]),
            'email' => $email,
            'role'  => 'FINANCE_OPERATOR'
        ];
        echo json_encode([
            'success' => true,
            'user' => $_SESSION['user']
        ]);
    }
    exit;
}

// 3. Permissive Registration
if ($action === 'register') {
    $input = json_decode(file_get_contents('php://input'), true);
    $name  = trim($input['name'] ?? 'Stakeholder');
    $email = strtolower(trim($input['email'] ?? 'user@karyaflow.internal'));
    $pass  = $input['password'] ?? 'password';
    $role  = $input['role'] ?? 'FINANCE_OPERATOR';

    try {
        $chk = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $chk->execute([$email]);
        $existing = $chk->fetch();

        if ($existing) {
            $userId = $existing['id'];
            $pdo->prepare("UPDATE users SET name = ?, role = ? WHERE id = ?")
                ->execute([$name, $role, $userId]);
        } else {
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hash, $role]);
            $userId = $pdo->lastInsertId();
        }

        $_SESSION['user'] = [
            'id'    => $userId,
            'name'  => $name,
            'email' => $email,
            'role'  => $role
        ];

        echo json_encode([
            'success' => true,
            'user' => $_SESSION['user']
        ]);
    } catch (Exception $e) {
        $_SESSION['user'] = [
            'id'    => 1,
            'name'  => $name,
            'email' => $email,
            'role'  => $role
        ];
        echo json_encode([
            'success' => true,
            'user' => $_SESSION['user']
        ]);
    }
    exit;
}

// 4. Update Profile
if ($action === 'update_profile') {
    if (empty($_SESSION['user'])) {
        echo json_encode(['success' => false, 'error' => 'Unauthenticated session']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $name  = trim($input['name'] ?? $_SESSION['user']['name']);
    $role  = $input['role'] ?? $_SESSION['user']['role'];
    $userId = $_SESSION['user']['id'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, role = ? WHERE id = ?");
        $stmt->execute([$name, $role, $userId]);
    } catch (Exception $e) {}

    $_SESSION['user']['name'] = $name;
    $_SESSION['user']['role'] = $role;

    echo json_encode([
        'success' => true,
        'user' => $_SESSION['user']
    ]);
    exit;
}

// 5. Logout
if ($action === 'logout') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    echo json_encode(['success' => true]);
    exit;
}