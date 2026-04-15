<?php

    // String
    function randomString(int $length = 16): string {
        $bytes = random_bytes($length + 5);
        $str   = str_replace(['/', '+', '='], random_int(0, 9), base64_encode($bytes));
        return substr($str, 0, $length);
    }

    function e(?string $value): string {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    function truncate(string $text, int $chars): string {
        if (strlen($text) <= $chars) return $text;
        $text = substr($text . ' ', 0, $chars);
        $pos  = strrpos($text, ' ');
        return ($pos !== false ? substr($text, 0, $pos) : $text) . '...';
    }

    function timeAgo(?string $datetime): string {
        if (!$datetime) return '-';
        $diff  = strtotime($datetime) - time();
        $future = $diff > 0;
        $diff  = abs($diff);
        foreach ([31536000=>'year',2592000=>'month',604800=>'week',86400=>'day',3600=>'hour',60=>'minute',1=>'second'] as $secs => $label) {
            if ($diff >= $secs) {
                $t = round($diff / $secs);
                $str = "$t {$label}" . ($t > 1 ? 's' : '');
                return $future ? "in $str" : "$str ago";
            }
        }
        return 'just now';
    }

    function initials(?string $string): string {
        if (empty($string)) return '';
        $result = '';
        foreach (explode(' ', $string) as $word) {
            $result .= mb_strtoupper($word[0] ?? '', 'UTF-8');
        }
        return substr($result, 0, 2);
    }

    function shortenClient(string $client): string {
        $client  = html_entity_decode($client);
        $client  = str_replace("'", '', $client);
        $cleaned = preg_replace('/[^a-zA-Z0-9&]+/', ' ', $client);
        $words   = explode(' ', trim($cleaned));
        $short   = '';
        if (count($words) === 1) {
            $w = $words[0];
            return strlen($w) <= 3 ? strtoupper($w) : strtoupper($w[0] . substr($w, -2));
        }
        foreach ($words as $w) {
            if (!in_array(strtolower($w), ['the','of','and']) || strlen($short) < 2) {
                $short .= $w[0];
            }
        }
        return strtoupper(substr($short, 0, 3));
    }

    // Security
    function validateCSRFToken(string $token): bool {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    function generateCSRFToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = randomString(32);
        }
        return $_SESSION['csrf_token'];
    }

    // Encryption (credential passwords) — mirrors itflow logic, uses session+cookie key
    function encryptCredential(string $plaintext): string {
        $iv         = randomString(16);
        $masterKey  = _getMasterKey();
        $ciphertext = openssl_encrypt($plaintext, 'aes-128-cbc', $masterKey, 0, $iv);
        return $iv . $ciphertext;
    }

    function decryptCredential(string $ciphertext): string {
        $iv         = substr($ciphertext, 0, 16);
        $data       = substr($ciphertext, 16);
        $masterKey  = _getMasterKey();
        return openssl_decrypt($data, 'aes-128-cbc', $masterKey, 0, $iv);
    }

    function _getMasterKey(): string {
        $sessionCipher = $_SESSION['user_encryption_session_ciphertext'] ?? '';
        $sessionIv     = $_SESSION['user_encryption_session_iv'] ?? '';
        $sessionKey    = $_COOKIE['user_encryption_session_key'] ?? '';
        return openssl_decrypt($sessionCipher, 'aes-128-cbc', $sessionKey, 0, $sessionIv);
    }

    // Files
    function checkFileUpload(array $file, array $allowedExtensions): string|false {
        if (empty($file['tmp_name'])) return false;
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) return false;
        if ($file['size'] > MAX_UPLOAD_MB * 1024 * 1024) return false;
        $hash = hash('md5', file_get_contents($file['tmp_name']));
        return $hash . randomString(2) . '.' . $ext;
    }

    // Redirect & flash
    function redirect(string $url = ''): never {
        $url = $url ?: ($_SERVER['HTTP_REFERER'] ?? '/');
        header("Location: $url");
        exit;
    }

    function flash(string $message, string $type = 'success'): void {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type']    = $type;
    }

    function getFlash(): ?array {
        if (empty($_SESSION['flash_message'])) return null;
        $flash = ['message' => $_SESSION['flash_message'], 'type' => $_SESSION['flash_type']];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return $flash;
    }

    // Networking
    function getSSL(string $host): array {
        $port = parse_url("//$host", PHP_URL_PORT) ?? 443;
        $name = parse_url("//$host", PHP_URL_HOST);
        $cert = ['success' => false, 'expire' => '', 'issued_by' => '', 'public_key' => ''];
        if (!filter_var($name, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) return $cert;
        $ctx  = stream_context_create(['ssl' => ['capture_peer_cert' => true, 'verify_peer' => false]]);
        $sock = @stream_socket_client("ssl://$name:$port", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $ctx);
        if ($sock) {
            $params = stream_context_get_params($sock);
            $parsed = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
            openssl_x509_export($params['options']['ssl']['peer_certificate'], $export);
            if ($parsed) {
                $cert = ['success' => true, 'expire' => date('Y-m-d', $parsed['validTo_time_t']), 'issued_by' => strip_tags($parsed['issuer']['O']), 'public_key' => $export];
            }
        }
        return $cert;
    }

    // Asset icon helper (from itflow)
    function assetIcon(string $type): string {
        return match($type) {
            'Laptop'          => 'laptop',
            'Desktop'         => 'desktop',
            'Server'          => 'server',
            'Printer'         => 'print',
            'Switch'          => 'network-wired',
            'Firewall/Router' => 'fire-alt',
            'Access Point'    => 'wifi',
            'Phone'           => 'phone',
            'Mobile Phone'    => 'mobile-alt',
            'Tablet'          => 'tablet-alt',
            'Virtual Machine' => 'cloud',
            default           => 'tag',
        };
    }

    function invoiceBadgeColor(string $status): string {
        return match($status) {
            'Sent'      => 'warning text-white',
            'Viewed'    => 'info',
            'Partial'   => 'primary',
            'Paid'      => 'success',
            'Cancelled' => 'danger',
            default     => 'secondary',
        };
    }

    function sanitizeUrl(?string $url): string {
        $allowed = ['http','https','ftp','ftps','sftp','ssh','rdp','vnc','sip','ldap','ldaps'];
        $scheme  = strtolower(parse_url($url ?? '', PHP_URL_SCHEME) ?? '');
        if ($scheme && !in_array($scheme, $allowed)) {
            $url = 'unsupported://' . ltrim(substr($url, strpos($url, ':') + 1), '/');
        }
        return e($url);
    }

    function generatePassword(int $level = 3): string {
        $adj   = ['Smart','Swift','Secure','Stable','Active','Rapid','Robust','Reliable','Bold','Clever'];
        $noun  = ['Computer','Server','Network','Firewall','Database','Tiger','Dragon','Panda','Falcon','Wolf'];
        $verb  = ['Connects','Secures','Encrypts','Updates','Scans','Compiles','Syncs','Runs','Jumps','Flies'];
        $adv   = ['Quickly','Securely','Silently','Rapidly','Smoothly','Reliably','Instantly','Swiftly'];
        $pass  = $adj[array_rand($adj)] . $noun[array_rand($noun)] . $verb[array_rand($verb)] . $adv[array_rand($adv)];
        $level = min(5, max(1, $level));
        $maps  = ['a'=>'@','e'=>'3','i'=>'!','o'=>'0','s'=>'$','t'=>'+'];
        $idx   = range(0, strlen($pass) - 1);
        shuffle($idx);
        for ($i = 0; $i < $level; $i++) {
            $c = strtolower($pass[$idx[$i]]);
            if (isset($maps[$c])) $pass[$idx[$i]] = $maps[$c];
        }
        return $pass . rand(pow(10, $level - 1), pow(10, $level) - 1);
    }