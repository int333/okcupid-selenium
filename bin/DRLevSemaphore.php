<?php
class DRLevSemaphore {
    private static $fileName = '/semaphore.data';
    private static $lockFileName = '/semaphore.lock';

    private static $timeout = 30; // wait for unlock timeout in seconds
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
    private static function lock() {
        $maxCnt = self::$timeout * 4;
        $cnt = 0;
        while (self::isLocked()) {
            $cnt++;
            if ($cnt > $maxCnt) {
                throw new Exception("wait lock timeout. semaphore is locked");
            }
            usleep(250000);
        }
    }
    private static function isLocked() {
        return !file_exists(__DIR__.'/'.self::$lockFileName) || (int) file_get_contents(__DIR__.'/'.self::$lockFileName) == 1;
    }
}