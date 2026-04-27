<?php

require_once __DIR__ . '/config.php';

function hh_is_local_request(): bool {
    $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
    $serverAddr = $_SERVER['SERVER_ADDR'] ?? '';
    $host = strtolower($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '');

    if (in_array($remoteAddr, ['127.0.0.1', '::1'], true) || in_array($serverAddr, ['127.0.0.1', '::1'], true)) {
        return true;
    }

    return str_contains($host, 'localhost') || str_contains($host, '127.0.0.1');
}

function hh_dev_bypass_available(): bool {
    return defined('IS_PREVIEW') && IS_PREVIEW && hh_is_local_request();
}

function hh_activate_dev_bypass(): void {
    $_SESSION['id'] = 0;
    $_SESSION['username'] = 'developer-preview';
    $_SESSION['password'] = '';
    $_SESSION['firstname'] = 'Local';
    $_SESSION['surname'] = 'Developer';
    $_SESSION['login'] = '1';
    $_SESSION['is_dev_bypass'] = true;
}

function hh_deactivate_dev_bypass(): void {
    unset(
        $_SESSION['id'],
        $_SESSION['username'],
        $_SESSION['password'],
        $_SESSION['firstname'],
        $_SESSION['surname'],
        $_SESSION['login'],
        $_SESSION['is_dev_bypass']
    );
}

function hh_handle_dev_bypass_request(string $defaultRedirect = 'dashboard.php'): void {
    if (!hh_dev_bypass_available()) {
        return;
    }

    $toggle = $_GET['dev_bypass'] ?? null;
    if ($toggle === null) {
        return;
    }

    $redirect = trim($_GET['redirect'] ?? $defaultRedirect);
    if ($redirect === '') {
        $redirect = $defaultRedirect;
    }

    if ($toggle === '1') {
        hh_activate_dev_bypass();
    } elseif ($toggle === '0') {
        hh_deactivate_dev_bypass();
    }

    header('Location: ' . $redirect);
    exit();
}

function hh_require_login(string $redirect = 'index.php'): void {
    if (!(isset($_SESSION['login']) && $_SESSION['login'] !== '')) {
        header('Location: ' . $redirect);
        exit();
    }
}

function hh_normalize_identity(string $value): string {
    return strtolower(trim(preg_replace('/\s+/', ' ', $value)));
}

function hh_is_admin_user(): bool {
    global $developer, $admin_usernames;

    $username = (string) ($_SESSION['username'] ?? '');
    $displayName = trim((string) ($_SESSION['firstname'] ?? '') . ' ' . (string) ($_SESSION['surname'] ?? ''));

    if (!empty($_SESSION['is_dev_bypass']) || $username === 'developer-preview') {
        return true;
    }

    if (is_array($admin_usernames) && in_array($username, $admin_usernames, true)) {
        return true;
    }

    if ($displayName !== '' && hh_normalize_identity($displayName) === hh_normalize_identity((string) $developer)) {
        return true;
    }

    return false;
}

function hh_require_admin(string $redirect = 'dashboard.php'): void {
    hh_require_login($redirect);

    if (!hh_is_admin_user()) {
        header('Location: ' . $redirect);
        exit();
    }
}

function hh_render_dev_banner(string $logoutPath = 'php/logout.php'): void {
    if (empty($_SESSION['is_dev_bypass'])) {
        return;
    }

    echo '<div class="alert alert-warning mb-0 rounded-0 border-0 text-center small" role="alert">';
    echo '<strong>Development Mode</strong> ';
    echo 'You are browsing with the localhost preview bypass enabled. ';
    echo '<a class="alert-link" href="' . htmlspecialchars($logoutPath, ENT_QUOTES) . '">Exit preview session</a>';
    echo '</div>';
}
