<?php

require_once __DIR__ . '/DRLevConfig.php';
require_once __DIR__ . '/functions.php';

require_once __DIR__ . '/../scripts/DRLevTempMail.php';

class DRLevDataMgr {
    protected $zipCodes = array();
    protected $nicks = array();
    protected $photos = array();
    protected $texts = array();
    protected $links = array();
    protected $newNickIndex = 0;
    protected $newLinkIndex = 0;
    protected static $instance;
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new DRLevDataMgr();
        }
        return self::$instance;
    }
    private function __construct() {
        $zipCodes = DRLevConfig::get('profile-zipcodes');
        if (file_exists($zipCodes)) {
            $zipCodes = file($zipCodes);
            foreach ($zipCodes as $zipCode) {
                $zipCode = trim($zipCode);
                if (!empty($zipCode)) {
                    $this->zipCodes[] = $zipCode;
                }
            }
        }
        if (count($this->zipCodes) == 0) {
            throw new Exception('Zip codes does not exists');
        }
        $nicks = DRLevConfig::get('profile-nicks');
        if (file_exists($nicks)) {
            $nicks = file($nicks);
            foreach ($nicks as $nick) {
                $nick = trim($nick);
                if (!empty($nick)) {
                    $this->nicks[] = $nick;
                }
            }
        }
        if (count($this->nicks) == 0)  {
            throw new Exception('Nicks does not exists');
        }
        $texts = DRLevConfig::get('profile-text');
        if (file_exists($texts)) {
            $texts = file($texts);
            $oneText = '';
            foreach ($texts as $text) {
                $text = trim($text);
                if (empty($text)) {
                    $this->texts[] = $oneText;
                    $oneText = '';
                } else {
                    $oneText.= empty($oneText) ? '' : "\n";
                    $oneText.= $text;
                }
            }
        }
        $links = DRLevConfig::get('profile-link');
        if (file_exists($links)) {
            $links = file($links);
            foreach ($links as $link) {
                $link = trim($link);
                if (!empty($link)) {
                    $this->links[] = $link;
                }
            }
        }
        $photosDir = DRLevConfig::get('profile-photos');
        if (is_dir($photosDir)) {
            $photos = scandir($photosDir);
            foreach ($photos as $photo) {
                if (!in_array($photo, array('.', '..'))) {
                    $photo = $photosDir.DIRECTORY_SEPARATOR.$photo;
                    if (file_exists($photo)) {
                        $this->photos[] = $photo;
                    }
                }

            }
        }
        if (count($this->photos) == 0)  {
            throw new Exception('Photos does not exists');
        }
    }

    public function generateProfileData() {
        $nick = $this->generateNick();
        $link = $this->generateLink();
        if (empty($nick)) {
            throw new Exception("No more nicks");
        }
        if (empty($link)) {
            throw new Exception("No more links");
        }
        $data = array(
            'nick' => $this->generateNick(),
            'country' => 'United States',
            'zipcode' => $this->zipCodes[rand(0, count($this->zipCodes) - 1)],
            'text' => $this->texts[rand(0, count($this->texts) - 1)]."\n".$this->generateLink(),
            'photo' => $this->photos[rand(0, count($this->photos) - 1)]
        );

        $data['password'] = $data['nick'].'1';
        $data['email'] = $data['nick'].DRLevTempMail::getEmailDomain();
        $d = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
        $m = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
        $y = rand(1989, 1997);
        $data['birthday'] = "{$y}.{$m}.{$d}";

        $this->fixDataEncoding($data);
        return $data;
    }

    public function generateNick() {
        if ($this->newNickIndex >= count($this->nicks)) {
            return '';
        }
        $nick = $this->nicks[$this->newNickIndex++];
        if ($nick[0] == '#') {
            return $this->generateNick();
        }
        $nicksFile = DRLevConfig::get('profile-nicks');
        $data = '';
        foreach($this->nicks as $i => $row) {
            if ($i > 0) {
                $data.= "\n";
            }
            if ($i < $this->newNickIndex && $row[0] != '#') {
                $data.='#';
            }
            $data.= $row;
        }
        file_put_contents($nicksFile, $data);
        return $nick;
    }

    public function generateLink() {
        if ($this->newLinkIndex >= count($this->links)) {
            return '';
        }
        $link = $this->links[$this->newLinkIndex++];
        if ($link[0] == '#') {
            return $this->generateLink();
        }
        $linksFile = DRLevConfig::get('profile-link');
        $data = '';
        foreach($this->links as $i => $row) {
            if ($i > 0) {
                $data.= "\n";
            }
            if ($i < $this->newLinkIndex && $row[0] != '#') {
                $data.='#';
            }
            $data.= $row;
        }
        file_put_contents($linksFile, $data);
        return $link;
    }

    public function setResult($data, $errorMessage = '') {
        $string = "{$data['nick']}|{$data['password']}|{$data['email']}\n";
        file_put_contents(DRLevConfig::get('profile-result'), $string, FILE_APPEND);
        if ($errorMessage) {
            file_put_contents('./log/error.txt', $errorMessage, FILE_APPEND);
        }
    }

    private function fixDataEncoding(&$data) {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                try {
                    $value = str_replace(chr(195), '', utf8_encode($value));
                    $data[$key] = $value;
                } catch (Exception $e) {
                    continue;
                }
            }
        }
    }
}