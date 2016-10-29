<?php

require_once __DIR__.'/DRLevScript.php';
require_once __DIR__.'/../lib/DRLevConfig.php';

class DRLevTempMail extends DRLevScript {
    protected $iterationCount = 5; // count of check iterations
    protected $iterationWait = 1; // time in seconds to wait before next iteration
    protected $iterationNo = 0;

    public function start() {
		if (!DRLevConfig::get('check-email', false)) {
			console("check email skipped\n");
			return false;
		}
        if (empty($this->data['email'])) {
            throw new Exception("Email is empty");
        }
        $md5 = md5($this->data['email']);
        console("try get email http://api.temp-mail.ru/request/mail/id/{$md5}/format/json");

        while ($this->iterationNo++ < $this->iterationCount) {
            try {
                console('.');
                $data = json_decode(file_get_contents("http://api.temp-mail.ru/request/mail/id/{$md5}/format/json"), true);
            } catch (Exception $e) {
                usleep($this->iterationWait * 1000000);
                continue;
            }
            if (isset($data['error'])) {
                if ($this->iterationNo <= $this->iterationCount) {
                    usleep($this->iterationWait * 1000000);
                } else {
                    $this->data['approve-url'] = DRLevConfig::get('url');
                    console("Failure to get approve email\n");
                    return false;
                }
            } else {
                $mailData = $data[0]['mail_text'];
                if (strpos($mailData, 'Sign In') !== false) {
                    $part = substr($mailData, strpos($mailData, 'Sign In') + 7);
                    $approveUrl = (trim(substr($part, 0, strpos($part, '-------'))));
                } else {
                    $mailData = $data[0]['mail_text_only'];
                    $marker = '<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word"';
                    $part = substr($mailData, strpos($mailData, $marker) + strlen($marker));
                    $approveUrl = (trim(substr($part, 0, strpos($part, 'style') - 2)));
                    $approveUrl = substr($approveUrl, 6);
                }
                $this->data['approve-url'] = $approveUrl;
                console("OK\n");
                console("approve url - {$approveUrl}\n");
                break;
            }
        }
    }

    public static function getEmailDomain() {
        $domains = explode('|', DRLevConfig::get('email-domain'));
        if (!empty($domains)) {
            return $domains[rand(0, count($domains) - 1)];
        }
    }
}
