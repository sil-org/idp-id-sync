<?php
namespace Sil\Idp\IdSync\common\components\adapters;

use InvalidArgumentException;
use Sil\Idp\IdBroker\Client\IdBrokerClient;
use Sil\Idp\IdSync\common\components\IdBrokerBase;

class IdpIdBroker extends IdBrokerBase
{
    protected $client = null;
    
    public function init()
    {
        if (empty($this->accessToken)) {
            throw new InvalidArgumentException('An access token is required.');
        }
        if (empty($this->baseUrl)) {
            throw new InvalidArgumentException('A base URL is required.');
        }
        parent::init();
    }
    
    public function authenticate(array $config = [])
    {
        return $this->getClient()->authenticate($config);
    }
    
    public function createUser(array $config = [])
    {
        return $this->getClient()->createUser($config);
    }
    
    public function deactivateUser(array $config = [])
    {
        return $this->getClient()->deactivateUser($config);
    }
    
    /**
     * @return IdBrokerClient
     */
    protected function getClient()
    {
        if ($this->client === null) {
            $this->client = new IdBrokerClient($this->baseUrl, $this->accessToken);
        }
        return $this->client;
    }
    
    public function getUser(array $config = [])
    {
        return $this->getClient()->getUser($config);
    }
    
    public function listUsers(array $config = [])
    {
        return $this->getClient()->listUsers($config);
    }
    
    public function setPassword(array $config = [])
    {
        return $this->getClient()->setPassword($config);
    }
    
    public function updateUser(array $config = [])
    {
        return $this->getClient()->updateUser($config);
    }
}
