<?php

class DRLevConfig {
    protected static $config = array();
    public static function get($key) {
        if (empty(self::$config)) {
            self::load();
        }
        if (array_key_exists($key, self::$config)) {
            return self::$config[$key];
        } else {
            return null;
        }
    }

    private static function load() {
        if (file_exists(__DIR__.'/../config.local.txt')) {
            $rows = file(__DIR__.'/../config.local.txt');
        } else {
            $rows = file(__DIR__.'/../config.txt');
        }
        foreach ($rows as $row) {
            $items = explode('=', $row);
            $key = trim(array_shift($items));
            $value = trim(implode('=', $items));
            self::$config[$key] = $value;
        }
    }
}