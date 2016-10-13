<?php

require_once('vendor/autoload.php');

require_once __DIR__ . '/lib/DRLevTest.php';
require_once __DIR__ . '/scripts/DRLevRegistration.php';
require_once __DIR__ . '/scripts/DRLevLogin.php';
require_once __DIR__ . '/scripts/DRLevFillProfile.php';
require_once __DIR__ . '/scripts/DRLevSetLike.php';
require_once __DIR__ . '/lib/DRLevDataMgr.php';

class MainTest extends DRLevTest {

    protected $repeatCount = 1;
    protected $data = array();

    public function setUp() {
        $this->url = DRLevConfig::get('url');
        parent::setUp();
    }

    public function testMain() {
        $count = max((int) DRLevConfig::get('repeat-count'), 1);
        $this->repeatCount = $count;
    }

    public function doRegistration() {

        $this->restartDriver();
        $this->driver->get($this->url);
        $this->data = DRLevDataMgr::getInstance()->generateProfileData();
        console("--REGISTRATION--\n");
        dump($this->data);
        $script = new DRLevRegistration($this->driver, $this->data);
        $script->start();

        console("--CHECK EMAIL--\n");
        $script = new DRLevTempMail($this->driver, $this->data);
        $script->start();
		try {
			$this->driver->get($this->data['approve-url']);
		} catch(UnknownServerException $e) {
			console("ERROR wrong approve url '{$this->data['approve-url']}'");
		}

//        $this->data['approve-url'] = "https://www.okcupid.com/l/.595de9AxiVga.4Fi9ob21lP2NmPXdlbGNvbWVfZW1haWwAADKTAJMuXZrCV_1CiQAk6g.5CmF8qV9AGymK6GvlSGsI1Ag@@uDM=";
//        console("--FILL PROFILE--\n");
//        $script = new DRLevFillProfile($this->driver, $this->data);
//        $script->start();

        console("--SET FILTER--\n");
        $script = new DRLevSetLike($this->driver, $this->data);
        $script->setSearchFilter();
//
//        $script = new DRLevLogin($this->driver, $this->data);
//        $script->start();
        console("--FINISH--\n");
        sleep(3);
    }

    public function tearDown() {
        while ($this->repeatCount-- > 0) {
            try{
                $this->doRegistration();
                DRLevDataMgr::getInstance()->setResult($this->data);
            } catch (Exception $e) {
                DRLevDataMgr::getInstance()->setResult($this->data, $e->getMessage());
                console("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!ERROR!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n");
                if (DRLevConfig::get('repeat-count') == 1) {
                    throw $e;
                } else {
                    console($e->getMessage()."\n");
                }
                continue;
            }
        }
        parent::tearDown();
    }
}