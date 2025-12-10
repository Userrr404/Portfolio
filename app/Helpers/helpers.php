<?php

function safe_fetch(PDOStatement|false $stmt)
{
    if (!$stmt) return null;
    try {
        return $stmt->fetch();
    } catch (Throwable $e) {
        app_log("Fetch Error", "error", ["error" => $e->getMessage()]);
        return null;
    }
}

function safe_fetch_all(PDOStatement|false $stmt)
{
    if (!$stmt) return [];
    try {
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        app_log("FetchAll Error", "error", ["error" => $e->getMessage()]);
        return [];
    }
}

function safe_query($sql)
{
    $pdo = DB::getInstance()->pdo();
    if (!$pdo) return false;

    try {
        return $pdo->query($sql);
    } catch (Throwable $e) {
        app_log("SQL Error", "error", ["sql" => $sql, "error" => $e->getMessage()]);
        return false;
    }
}

function get_user_ip() {
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}

function get_user_agent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
}

function get_device_type() {
    $ua = get_user_agent();
    if (preg_match('/mobile/i', $ua)) return "Mobile";
    if (preg_match('/tablet/i', $ua)) return "Tablet";
    return "Desktop";
}

function get_browser_name() {
    $ua = get_user_agent();

    if (strpos($ua, 'Chrome') !== false) return 'Chrome';
    if (strpos($ua, 'Firefox') !== false) return 'Firefox';
    if (strpos($ua, 'Safari') !== false) return 'Safari';
    if (strpos($ua, 'Edge') !== false) return 'Edge';

    return 'Unknown';
}

function get_country_from_ip($ip) {
    return ($ip === '127.0.0.1' || $ip === '::1') ? "Localhost" : "Unknown";
}

