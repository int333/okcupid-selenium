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
     * @param bool $throw
     * @return int|string
     * @throws NoSuchElementException
     */
    protected function clickElement($selector, $tryIterations = 1, $throw = true) {
        if (is_array($selector)) {
            $err = null;
            foreach ($selector as $i => $try) {
                try {
                    $this->clickElement($try, $tryIterations, $throw);
                    return $i;
                } catch (NoSuchElementException $e) {
                    $err = $e;
                    continue;
                } catch (TimeOutException $e) {
                    $err = $e;
                    continue;
                }
            }
            if ($err && $throw) {
                if ($throw) {
                    throw $err;
                } else {
                    return false;
                }
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
            } catch (TimeOutException $e) {
                $lastErr = $e;
            }

            if (!$done && $lastErr) {
                if ($throw) {
                    throw $lastErr;
                } else {
                    return false;
                }
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
        if (is_array($selector)) {
            foreach ($selector as $item) {
                if ($this->fillElement($item, $value, $checkError)) {
                    return true;
                }
            }
            return false;
        }
        if ($this->clickElement($selector, 1, false) === false) {
            return false;
        }
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
            $element = $this->driver->findElement($by);
            return $element->isDisplayed();
        } catch (NoSuchElementException $e) {
            return false;
        }
    }

    public function closePopup() {
        $this->driver->executeScript("jQuery('#windowshade').find('span.icon.i-close').click();");
    }
} 
