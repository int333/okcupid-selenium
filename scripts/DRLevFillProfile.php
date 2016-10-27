<?php

require_once __DIR__.'/DRLevScript.php';
require_once __DIR__.'/DRLevSetLike.php';

class DRLevFillProfile extends DRLevScript {
    public function start() {
        $this->driver->get($this->data['approve-url']);
        $this->driver->get(DRLevConfig::get('url').'/profile');
        console("set photo...");
        $this->clickElement(array("xpath=//div[contains(@class, 'userinfo2015-thumb-upload-content')]", "xpath=//div[@id='profile_thumbs']//button")) ;
        $fileInput = $this->driver->findElement($this->getByFromSelector("xpath=//input[@id='okphotos_file_input']"));
        $fileInput->sendKeys($this->data['photo']); 
        sleep(15);
        $this->clickElement("xpath=//div[@id='okphotos_edit']//a[@id='okphotos_edit_next']");
        $this->clickElement("xpath=//div[@id='okphotos_finished']//a[@id='done_uploading']");
        console("OK\n");

        $this->clickElement("xpath=//div[@id='windowshade']", 1, false);
        console("set text...");
        stepSleep();
        $this->fillElement("xpath=//div[@id='react-profile-essays']//form//textarea[@name='essay']", $this->data['text']);
        stepSleep();
        $this->clickElement("xpath=//div[@id='react-profile-essays']//button[contains(text(), 'Save')]");
        stepSleep();
        console("OK\n");

        $this->driver->get(DRLevConfig::get('url').'/match');
        $liker = new DRLevSetLike($this->driver, $this->data);
        $liker->setLikes(3);
        console("OK\n");

        console("try anser questions...");
        $this->driver->get(DRLevConfig::get('url').'/home');
        try{
            $i = 10;
            while (true && $i-- > 0) {
                $xpath = "xpath=//div[@class='binary_questions_block']//button[".rand(1, 2)."]";
                $this->clickElement($xpath);
                sleep(2);
            }
            console("OK\n");
        } catch (NoSuchElementException $e) {
            console("ERROR\n");
        } catch (ElementNotVisibleException $e) {
            console("ERROR\n");
        }
    }
}
