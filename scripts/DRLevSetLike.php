<?php
require_once __DIR__.'/DRLevScript.php';
class DRLevSetLike extends DRLevScript {
    public function start() {
        $this->driver->get($this->data['approve-url']);
        $this->driver->get(DRLevConfig::get('url').'/profile');
        console("set search filter...");
        $this->clickElement("#what_i_want_react");
        $ch = $this->driver->findElement($this->getByFromSelector("xpath=//fieldset[@class='wiw-form-nearme']//input"));
        if ($ch->getAttribute('checked') == 'true') {
            $this->clickElement("xpath=//fieldset[@class='wiw-form-nearme']//span[@class='oknf-switch-decoration']");
        }
        $ch = $this->driver->findElement($this->getByFromSelector("xpath=//fieldset[@class='wiw-form-lookingfor']//*[@value='casual_sex']//ancestor::label/input"));
        if ($ch->getAttribute('checked') != 'true') {
            $this->clickElement("xpath=//fieldset[@class='wiw-form-lookingfor']//*[@value='casual_sex']//ancestor::label[1]");
        }
        $this->clickElement("xpath=//fieldset[@class='reactmodal-buttons']//span[contains(text(), 'Save')]");
        console("OK\n");

        $pagesCount = max((int) DRLevConfig::get('like-pages-count'), 1);
        $perPageCount = max((int) DRLevConfig::get('like-per-page-count'), 1);
        $i = 1;
        while($pagesCount-- > 0) {
            console("page ".$i++."\n");
            $this->driver->get(DRLevConfig::get('url').'/match');
            $this->setFilter("Last online");
            sleep(5);
            $this->setLikes($perPageCount);
        }
    }
    public function setFilter($filter) {
        $this->clickElement("xpath=//div[@class='match-filters-in-results']//a[contains(@class,'chosen-single')]");
        $this->clickElement("xpath=//div[@class='match-filters-in-results']//li[contains(text(),'{$filter}')]");
    }
    public function setLikes($count = 1) {
        console("set likes {$count}:");
        for($i = 0; $i < $count; $i++) {
            try{
                $this->driver->executeScript("jQuery('div.match-results-cards').find('button.binary_rating_button.silver.flatbutton').not('.liked')[0].click()");
                console(".");
            } catch (UnknownServerException $e) {
                sleep(2);
                console("e");
                continue;
            }
            usleep(500000);
        }
        console("\n");
        return;
    }
}