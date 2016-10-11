<?php

require_once __DIR__ . '/DRLevScript.php';
require_once __DIR__ . '/DRLevRuCaptcha.php';
require_once __DIR__ . '/DRLevTempMail.php';

class DRLevRegistration extends DRLevScript {

    public function start() {
        $this->clickElement('.next_page');
        stepSleep();
        $this->fillForm();
        stepSleep();
        $this->clickElement("xpath=//div[@id='form_container']//button[contains(@class, 'next_page')]");
        stepSleep();
        $this->fillLogin();
        stepSleep();
        $this->clickElement("xpath=//div[@id='credentials']//button[contains(text(), 'Done!')]");
        stepSleep();
        $captcha = new DRLevRuCaptcha($this->driver, $this->data);
        $captcha->start();
        $this->clickElement("xpath=//div[@id='signup_captcha']/following-sibling::*[contains(text(), 'Done!')]");
    }

    protected function fillForm() {
        $birthday = explode('.', $this->data['birthday']);
        $this->fillElement('#birthday', $birthday[2]);
        $this->fillElement('#birthmonth', $birthday[1]);
        $this->fillElement('#birthyear', $birthday[0]);
        $this->selectItem('#country_selectContainer', $this->data['country']);
        $this->fillElement('#zip_or_city', $this->data['zipcode']);
        $this->fillElement('#email1', $this->data['email']);
        $this->fillElement('#email2', $this->data['email']);
    }

    protected function fillLogin() {
        $login = $this->data['nick'];
        if (!$this->fillElement('#screenname_input', $login, true)) {
            try {
                $login = $this->driver->findElement($this->getByFromSelector('xpath=//input[@id=\'screenname_input\']/ancestor::div[1]//li[2]'))->getText();
            } catch (NoSuchElementException $e) {
                $this->data['nick'] = DRLevDataMgr::getInstance()->generateNick();
                $this->fillLogin();
                return;
            }
            $this->clickElement("xpath=//input[@id='screenname_input']/ancestor::div[1]//li[2]");
            $this->data['nick'] = $login;
            usleep(250000);
        }
        $this->fillElement('#password_input', $login.'1');
    }

    protected function getCountry() {
        return 'United States';
    }

    protected function getZipOrCity($country) {
        return '10001';
    }

    protected function getEmail() {
        if (empty($this->email)) {
            $this->email = $this->getRandomEmail();
        }
        return $this->email;
    }
    protected function getRandomEmail() {
        return 'tratata3@polyfaust.com';
    }

    protected function getUserName() {
        if (empty($this->name)) {
            $this->name = $this->getRandomUserName();
        }
        return $this->name;
    }
    protected function getRandomUserName() {
        return 'tratata3';
    }
} 