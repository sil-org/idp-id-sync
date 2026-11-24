<?php

namespace Sil\Idp\IdSync\_tests\phpunit;

use Exception;
use GuzzleHttp\Command\Command;
use GuzzleHttp\Command\Exception\CommandClientException;
use GuzzleHttp\Command\Exception\CommandException;
use PHPUnit\Framework\TestCase;
use Sil\Idp\IdBroker\Client\ServiceException;
use Sil\Idp\IdBroker\Client\ValidationException;
use Sil\Idp\IdSync\common\sync\Synchronizer;

class SynchronizerTest extends TestCase
{
    public function testIsBrokerDataErrorForHttp422()
    {
        $e = new ServiceException('Username has already been taken', 0, 422);
        $this->assertTrue(Synchronizer::isBrokerDataError($e));
    }

    public function testIsBrokerDataErrorForOtherHttpStatus()
    {
        $e = new ServiceException('Server error', 0, 500);
        $this->assertFalse(Synchronizer::isBrokerDataError($e));
    }

    public function testIsBrokerDataErrorForClientSideValidationError()
    {
        $e = new ValidationException(
            'Validation errors: [username] is a required string',
            new Command('createUser')
        );
        $this->assertTrue(Synchronizer::isBrokerDataError($e));
    }

    public function testIsBrokerDataErrorForCommandExceptionWithValidationMessage()
    {
        $e = new CommandException(
            'Validation errors: [username] is a required string',
            new Command('createUser')
        );
        $this->assertFalse(Synchronizer::isBrokerDataError($e));
    }

    public function testIsBrokerDataErrorForOtherCommandException()
    {
        $e = new CommandException(
            'There was an error executing the createUser command: cURL error 7',
            new Command('createUser')
        );
        $this->assertFalse(Synchronizer::isBrokerDataError($e));
    }

    public function testIsBrokerDataErrorForCommandSubclass()
    {
        $e = new CommandClientException(
            'Validation errors: something',
            new Command('createUser')
        );
        $this->assertFalse(Synchronizer::isBrokerDataError($e));
    }

    public function testIsBrokerDataErrorForGenericException()
    {
        $e = new Exception('Validation errors: something');
        $this->assertFalse(Synchronizer::isBrokerDataError($e));
    }
}
