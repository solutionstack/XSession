<?php


namespace XSessionTest;

include_once 'smartloader.php';

use PHPUnit\Framework\TestCase;
use solutionstack\XSession;

class XSessionTest extends TestCase {

    protected $s;

    protected function setUp() {

        if (isset($_SESSION)) {
            unset($_SESSION);
        }

        //remove files from the session_save_path dir
        \shell_exec('rm -rf App/XSession/sess/*');
    }

    public function testSessionCreated() {

        $this->s = new XSession("test_session", 300);
        $this->s->start(); 

	//make sure the instance of the XSession class was created
	$this->assertInstanceOf( XSession::class, $this->s);	

$s_dir = __DIR__."/App/XSession/sess";
  $r = shell_exec('dir '. $s_dir);
echo $r;
	//make sure session file gets created, in the save path
	$this->assertFileExists(__DIR__."/App/XSession/sess/sess_".$this->s->getSID(), "session file not created in save path");

	  $this->s->put("bar",2000);
	
	//read the stored session data
        $this->assertEquals(  $this->s->get("bar"), 2000, "Adding data to session failed");


	$this->s->end();
	
	$this->assertTrue(isset($_SESSION) === false);
	
	return $this->s;

    }



    protected function tearDown() {
	
 	//remove files from the session_save_path dir
        shell_exec('rm -rf App/XSession/sess/*');


        
    }

}
