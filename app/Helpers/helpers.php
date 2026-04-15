<?php

    namespace App\Helpers;

    use Core\Database;

    class Helpers {

        public static function config(string $key, mixed $default = null): mixed {
            static $cache = [];
            [$file, $item] = explode('.', $key, 2) + [1 => null];
            if (!isset($cache[$file])) {
                $path = __DIR__ . '/../../config/' . $file . '.php';
                $cache[$file] = file_exists($path) ? require $path : [];
            }
            return $item ? ($cache[$file][$item] ?? $default) : ($cache[$file] ?? $default);
        }

        public static function lookupPermission(string $module, int $roleId): int|false {
            $db  = Database::getInstance();
            $row = $db->fetch(
                "SELECT urp.user_role_permission_level
                FROM modules m
                JOIN user_role_permissions urp ON m.module_id = urp.module_id
                WHERE m.module_name = ? AND urp.user_role_id = ?",
                [$module, $roleId]
            );
            return $row ? (int) $row['user_role_permission_level'] : false;
        }

        public static function enforcePermission(string $module, int $level = PERM_READ): void {
            $permitted = self::lookupPermission($module, $_SESSION['user_role'] ?? 0);
            if (!$permitted || $permitted < $level) {
                flash(ROLE_CHECK_FAILED, 'danger');
                redirect('/');
            }
        }

        public static function isAdmin(): bool {
            return !empty($_SESSION['is_admin']);
        }

        public static function logAction(string $type, string $action, string $description, int $clientId = 0, int $entityId = 0): void {
            $db = Database::getInstance();
            $db->query(
                "INSERT INTO logs SET log_type=?, log_action=?, log_description=?, log_ip=?, log_user_agent=?, log_client_id=?, log_user_id=?, log_entity_id=?",
                [$type, $action, $description, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '', $clientId, $_SESSION['user_id'] ?? 0, $entityId]
            );
        }

        public static function notify(string $type, string $details, ?string $action = null, int $clientId = 0, int $entityId = 0): void {
            $db    = Database::getInstance();
            $users = $db->fetchAll("SELECT user_id FROM users WHERE user_type=1 AND user_status=1 AND user_archived_at IS NULL");
            foreach ($users as $user) {
                $db->query(
                    "INSERT INTO notifications SET notification_type=?, notification=?, notification_action=?, notification_client_id=?, notification_entity_id=?, notification_user_id=?",
                    [$type, $details, $action, $clientId, $entityId, $user['user_id']]
                );
            }
        }
    }