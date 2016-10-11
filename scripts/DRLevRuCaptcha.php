<?php

require_once __DIR__ . '/DRLevScript.php';

class DRLevRuCaptcha extends DRLevScript {
    public function start() {
        /** @var RemoteWebElement $el */
        $el = $this->driver->findElement(WebDriverBy::className('g-recaptcha'));
        $googlekey = $el->getAttribute('data-sitekey');
        $url = DRLevConfig::get('url');
        $apiKey = DRLevConfig::get('rucaptcha-api-key');

        console("request captcha result\n");
        $captchaResponse = json_decode(file_get_contents("http://rucaptcha.com/in.php?key={$apiKey}&method=userrecaptcha&googlekey={$googlekey}&pageurl={$url}&json=1"), true);

        if ($captchaResponse['status'] == 1) {
            $captchaId = $captchaResponse['request'];
        } else {
            throw new Exception('Error response from rucaptcha - '.json_encode($captchaResponse));
        }

        $captchaResult = '';
        for($i = 0; $i < 60; $i++) {
            $captchaResponse = json_decode(file_get_contents("http://rucaptcha.com/res.php?key={$apiKey}&action=get&id={$captchaId}&json=1"), true);
            if ($captchaResponse['status'] == 1) {
                $captchaResult = $captchaResponse['request'];
                console("done\n");
                break;
            }
            $captchaResponse = json_encode($captchaResponse);
            console("{$captchaResponse}\n");
            sleep(2);
        }
        if (empty($captchaResult)) {
            throw new Exception('Captcha is not done');
        }

        $textId = 'g-recaptcha-response';
        $this->driver->executeScript("document.getElementById('{$textId}').style.display = 'block';");
        $this->driver->executeScript("document.getElementById('{$textId}').value = '{$captchaResult}';");
        $this->driver->executeScript("document.getElementById('{$textId}').style.display = 'none';");
    }
}