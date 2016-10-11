<?php

abstract class DRLevScript {

    /**
     * @var RemoteWebDriver
     */
    protected $driver;
    protected $data;
    public function __construct(RemoteWebDriver $driver, &$data) {
        $this->driver = $driver;
        $this->data = &$data;
    }

    /**
     * abstract methods
     */
    public function start() {}
    public function finish() {}

    /**
     * @param $selector
     * @return null|WebDriverBy
     */
    protected function getByFromSelector($selector) {
        $by = null;
        if ($selector[0] == '#') {
            $by = WebDriverBy::id(substr($selector, 1));
        } else if ($selector[0] == '.') {
            $by = WebDriverBy::className(substr($selector, 1));
        } else if (strpos($selector, 'xpath=') === 0) {
            $by = WebDriverBy::xpath(substr($selector, 6));
        } else {
            $by = WebDriverBy::cssSelector($selector);
        }
        return $by;
    }

    /**
     * @param $selector
     * @param int $tryIterations
     * @return bool
     * @throws NoSuchElementException
     */
    protected function clickElement($selector, $tryIterations = 1) {
        if (is_array($selector)) {
            $err = null;
            foreach ($selector as $i => $try) {
                try {
                    $this->clickElement($try, $tryIterations);
                    return $i;
                } catch (NoSuchElementException $e) {
                    $err = $e;
                    continue;
                }
            }
            if ($err) {
                throw $err;
            } else {
                return;
            }
        }
        $by = $this->getByFromSelector($selector);
        while($tryIterations-- > 0 ) {
            $done = false;
            $lastErr = null;
            try {
                $this->driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable($by));
                $done = true;
                break;
            } catch (NoSuchElementException $e) {
                $lastErr = $e;
            }

            if (!$done && $lastErr) {
                throw $lastErr;
            }
        }
        $this->driver->findElement($by)->click();
    }

    /**
     * @param $selector
     * @param $value
     * @param bool $checkError
     * @return bool
     */
    protected function fillElement($selector, $value, $checkError = false) {
        $this->clickElement($selector);
        $by = $this->getByFromSelector($selector);
        $this->driver->findElement($by)->clear();
        $this->driver->getKeyboard()->sendKeys($value);
        if ($checkError) {
            $this->driver->findElement(WebDriverBy::tagName('body'))->click();
            sleep(3);
            $by = $this->getByFromSelector($selector);
            $el = $this->driver->findElement($by);
            try {
                $el->findElement(WebDriverBy::xpath("../ancestor-or-self::div[contains(@class, 'statuserror')]"));
                return false;
            } catch (NoSuchElementException $e) {
                return true;
            }
        }
        return true;
    }

    /**
     * @param $selector
     * @param $value
     * @return bool
     */
    protected function selectItem($selector, $value) {
        $this->clickElement($selector);
        $selector = substr($selector, 1);
        $by = WebDriverBy::xpath("//div[@id='$selector']//li[contains(text(), '$value')]");
        $this->driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable($by));
        $this->driver->findElement($by)->click();
        return true;
    }

    public function isElementPresent($selector) {
        $by = $this->getByFromSelector($selector);
        try {
            $this->driver->findElement($by);
            return true;
        } catch (NoSuchElementException $e) {
            return false;
        }
    }
} 