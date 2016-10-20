<?php
class DRLevSemaphore {
    private static $fileName = '/semaphore.lock';
    private static function _readFileData() {
        if (file_exists(__DIR__.'/'.self::$fileName)) {
            $data = file_get_contents(__DIR__.'/'.self::$fileName);
            $data = unserialize($data);
            return $data;
        } else {
            return null;
        }
    }
    private static function _writeFileData($data = array()) {
        $file = fopen(__DIR__.'/'.self::$fileName, 'c+');
        flock($file, LOCK_EX);
        $data = serialize($data);
        fwrite($file, $data);
        flock($file, LOCK_UN);
        fclose($file);
    }
    public static function read($varName, $defValue = null) {
        $data = self::_readFileData();
        if ($data && array_key_exists($varName, $data)) {
            return $data[$varName];
        } else {
            return $defValue;
        }
    }
    public static function write($varName, $varValue) {
        $data = self::_readFileData();
        if (!$data) {
            $data = array($varName => $varValue);
        } else {
            $data[$varName] = $varValue;
        }
        self::_writeFileData($data);
        return true;
    }
}