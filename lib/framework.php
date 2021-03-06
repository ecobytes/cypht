<?php

/**
 * Initial setup
 * @package framework
 * @subpackage setup
 */

define('VERSION', .1);

/* load the framework */
require APP_PATH.'lib/module.php';
require APP_PATH.'lib/modules.php';
require APP_PATH.'lib/modules_exec.php';
require APP_PATH.'lib/config.php';
require APP_PATH.'lib/auth.php';
require APP_PATH.'lib/oauth2.php';
require APP_PATH.'lib/session_base.php';
require APP_PATH.'lib/session_php.php';
require APP_PATH.'lib/session_db.php';
require APP_PATH.'lib/session_memcached.php';
require APP_PATH.'lib/format.php';
require APP_PATH.'lib/dispatch.php';
require APP_PATH.'lib/request.php';
require APP_PATH.'lib/cache.php';
require APP_PATH.'lib/output.php';
require APP_PATH.'lib/crypt.php';
require APP_PATH.'lib/crypt_sodium.php';
require APP_PATH.'lib/sodium_compat.php';
require APP_PATH.'lib/db.php';
require APP_PATH.'lib/servers.php';
require APP_PATH.'lib/api.php';

/* load the site module set library if found */
if (is_readable(APP_PATH.'modules/site/lib.php')) {
    require APP_PATH.'modules/site/lib.php';
}

/* load random bytes polyfill if needed */
if (!function_exists('random_bytes')) {
    require APP_PATH.'third_party/random_compat/lib/random.php';
}

/* check for and load the correct libsodium interface */
if (!defined('LIBSODIUM')) {
    if (extension_loaded('libsodium') && function_exists('\Sodium\crypto_pwhash_str_verify')) {
        define('LIBSODIUM', true);
        class Hm_Sodium_Compat extends Hm_Sodium_PECL {}
    }
    if (extension_loaded('sodium') && function_exists('sodium_crypto_pwhash_str_verify')) {
        define('LIBSODIUM', true);
        class Hm_Sodium_Compat extends Hm_Sodium_PHP {}
    }
    if (!defined('LIBSODIUM')) {
        define('LIBSODIUM', false);
    }
}

if (!class_exists('Hm_Functions')) {
    /**
     * Used to override built in functions that break unit tests
     * @package framework
     * @subpackage setup
     */
    class Hm_Functions {

        /**
         * @param string $name
         * @param string $value
         * @return boolean
         */
        public static function setcookie($name, $value, $lifetime=0, $path='', $domain='', $secure=false, $html_only=false) {
            $prefix = $lifetime != 0 && $lifetime < time() ? 'Deleting' : 'Setting';
            Hm_Debug::add(sprintf('%s cookie: name: %s, lifetime: %s, path: %s, domain: %s, secure: %s, html_only %s',
                $prefix, $name, $lifetime, $path, $domain, $secure, $html_only));
            return setcookie($name, $value, $lifetime, $path, $domain, $secure, $html_only);
        }

        /**
         * @param string $header
         * @return null
         */
        public static function header($header) {
            return header($header);
        }

        /**
         * @param string $msg
         * @return null
         */
        public static function cease($msg='') {
            die($msg);
        }

        /**
         * @return boolean
         */
        public static function session_destroy() {
            if (session_status() === PHP_SESSION_ACTIVE) {
                return session_destroy();
            }
            return false;
        }

        /**
         * @return boolean
         */
        public static function session_start() {
            return session_start();
        }

        /**
         * @return boolean
         */
        public static function error_log($str) {
            return error_log($str);
        }

        /**
         * @param resource|false $handle
         * @param integer $name
         */
        public static function c_setopt($handle, $name, $value) {
            if ($handle !== false) {
                curl_setopt($handle, $name, $value);
            }
        }

        /**
         * @return resource|false
         */
        public static function c_init() {
            return curl_init();
        }

        /**
         * @param resource|false $handle
         */
        public static function c_exec($handle) {
            return curl_exec($handle);
        }

        /**
         * @param string $func
         */
        public static function function_exists($func) {
            return function_exists($func);
        }

        /**
         * @param string $class
         */
        public static function class_exists($class) {
            return class_exists($class, false);
        }

        /**
         * @param integer $size
         */
        public static function random_bytes($size) {
            return random_bytes($size);
        }

        /**
         * @return Memcached
         */
        public static function memcached() {
            return new Memcached();
        }

        /**
         * @param string $type input type
         * @param array $filters filter list
         * @return array filtered list
         */
        public static function filter_input_array($type, $filters) {
            return filter_input_array($type, $filters, false);
        }
    }
}
