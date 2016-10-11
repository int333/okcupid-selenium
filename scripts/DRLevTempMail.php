<?php

require_once __DIR__.'/DRLevScript.php';
require_once __DIR__.'/../lib/DRLevConfig.php';

class DRLevTempMail extends DRLevScript {
    protected $iterationCount = 120; // count of check iterations
    protected $iterationWait = 1; // time in seconds to wait before next iteration
    protected $iterationNo = 0;

    public function start() {
        if (empty($this->data['email'])) {
            throw new Exception("Email is empty");
        }

        while ($this->iterationNo++ < $this->iterationCount) {
            $md5 = md5($this->data['email']);
            try {
                console("try get email http://api.temp-mail.ru/request/mail/id/{$md5}/format/json ..");
                $data = json_decode(file_get_contents("http://api.temp-mail.ru/request/mail/id/{$md5}/format/json"), true);
                console("OK\n");
            } catch (Exception $e) {
                console("ERROR\n");
                usleep($this->iterationWait * 1000000);
                continue;
            }
            if (isset($data['error'])) {
                if ($this->iterationNo <= $this->iterationCount) {
                    usleep($this->iterationWait * 1000000);
                } else {
                    throw new Exception('Failure to get approve email');
                }
            } else {
                $mailData = $data[0]['mail_text'];
                $part = substr($mailData, strpos($mailData, 'Sign In') + 7);
                $approveUrl = (trim(substr($part, 0, strpos($part, '-------'))));
                $this->data['approve-url'] = $approveUrl;
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