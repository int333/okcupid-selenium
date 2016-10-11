<?php

require_once __DIR__.'/DRLevConfig.php';
require_once __DIR__.'/functions.php';

class DRLevTest extends PHPUnit_Framework_TestCase {
    protected $url = '';
    /**
     * @var RemoteWebDriver
     */
    protected $driver;

    protected $host = 'http://localhost:4444/wd/hub';
    public function setUp() { }

    public function restartDriver() {
        if ($this->driver) {
            $this->driver->quit();
        }
        $capabilities = DesiredCapabilities::chrome();
        $this->driver = RemoteWebDriver::create($this->host, $capabilities, 5000);
        $this->driver->manage()->window()->maximize();
    }

    public function tearDown() {
        $this->driver->quit();
    }
} 