<?php
/**
 * TaskValidatorTest.php
 *
 * @project take-home
 *
 */
namespace App\Tests\Utitlity;

use App\Utility\TaskValidator;
use PHPUnit\Framework\TestCase;


/**
 * Class TaskValidatorTest
 *
 * @package App\Tests\Utitlity
 */
class TaskValidatorTest extends TestCase
{
    public $validator;
    
    public function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->validator = new TaskValidator();
    }
    
    public function testIsValidCommand()
    {
        $command = "some command";
        $this->assertTrue($this->validator->isValidCommand($command));
    }
    
    public function testIsValidCommandShouldFail()
    {
        $command = "";
        $this->assertFalse($this->validator->isValidCommand($command));
    }
    
    public function testIsValidTimestamp()
    {
        $ts = time();
        $this->assertTrue($this->validator->isValidTimestamp($ts));
    }

    public function testIsValidTimestampShouldFail()
    {
        $ts = "01/21/2020";
        $this->assertFalse($this->validator->isValidTimestamp($ts));
    
    }
    
    public function testIsValidId()
    {
        $id = 124;
        
        $this->assertTrue($this->validator->isValidId($id));
    }

    public function testIsValidIdWithStringShouldFail()
    {
        $id = "id";
    
        $this->assertFalse($this->validator->isValidId($id));
    
    }

    public function testIsValidIdWithEmptyIdShouldFail()
    {
        $id = null;
    
        $this->assertFalse($this->validator->isValidId($id));
    
    }
    
}