<?php

require_once __DIR__ . '/DRLevScript.php';
require_once __DIR__ . '/DRLevRuCaptcha.php';
require_once __DIR__ . '/DRLevTempMail.php';

class DRLevRegistration extends DRLevScript {

    public function start() {
	$gender = DRLevConfig::get('gender', 'Woman');
	$this->selectItem('#gender_dropdownContainer', $gender);
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
        $this->fill3Steps();
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

    protected function fill3Steps($fullStart = true) {
        sleep(3);
        $this->closePopup();
        console("set photo...");
		if ($fullStart) {
			$this->clickElement("xpath=//*[@class='photoupload-uploader']|//*[@id='profile_card_add_photo']");
		}
        $fileInput = $this->driver->findElement($this->getByFromSelector("xpath=//input[@id='okphotos_file_input']"));
        $fileInput->sendKeys($this->data['photo']);
		if ($this->isElementPresent("xpath=//div[@id='okphotos_upload']")) {
			unlink($this->data['photo']);
			$this->data['photo'] = DRLevDataMgr::getInstance()->generatePhoto();
			console("FAIL\ntry again {$this->data['photo']}\n");
			return $this->fill3Steps(false);
		}
        console("OK\n");
        unlink($this->data['photo']);
        sleep(2);
        stepSleep();
        $this->closePopup();
        console("set text...");

        $this->fillElement("xpath=//textarea[@id='profile_textarea']|//textarea[@class='oknf-textarea']", $this->data['text']);
        stepSleep();
        stepSleep();
        $this->clickElement("xpath=//button/descendant-or-self::*[contains(text(), 'Done')]");
        stepSleep();
        console("OK\n");
        sleep(2);
        $this->closePopup();
        console("answer questions...\n");
        while (true) {
            $rand = rand(1, 2);
            $selector = "xpath=//div[@id='answer_buttons']/button[{$rand}]|//div[@class='obquestions-buttons']/button[{$rand}]";
            if ($this->isElementPresent($selector)) {
                $this->clickElement($selector);
                console(($rand == 1 ? "'No'" : "'Yes'")." ");
                sleep(2);
            } else {
                break;
            }
        }
        console("OK\n");
        sleep(2);
        stepSleep();
        console('');
        if ($this->clickElement("xpath=//input[@value='casual_sex']", 5, false)) {
            $this->clickElement("xpath=//button/descendant-or-self::*[contains(text(), 'Continue')]");
            sleep(2);
        }
        console("3 likes...");
        if (DRLevConfig::get('set-3-likes', 1) == 0) {
            console("SKIPPED\n");
        } else {
            $this->driver->executeScript("jQuery('div.oblikes-match:lt(3)').find('button').click(); jQuery('div.user_card:lt(3)').find('a.rate_btn.flatbutton.silver').each(function(i, e){var el = e, clickFn = function(){el.click()}; setTimeout(clickFn, 1000 * i)});");
                console("OK\n");
                sleep(2);
        }
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
