<?php
namespace Sil\Idp\IdSync\common\interfaces;

interface IdBrokerInterface
{
    public function authenticate(array $config = []);
    public function createUser(array $config = []);
    public function deactivateUser(array $config = []);
    public function getUser(array $config = []);
    public function listUsers(array $config = []);
    public function setPassword(array $config = []);
    public function updateUser(array $config = []);
}
