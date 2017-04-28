<?php
namespace Sil\Idp\IdSync\common\interfaces;

interface IdBrokerInterface
{
    public function activateUser(string $employeeId);
    public function authenticate(string $username, string $password);
    public function createUser(array $config = []);
    public function deactivateUser(string $employeeId);
    public function getUser(string $employeeId);
    public function listUsers($fields = null);
    public function setPassword(string $employeeId, string $password);
    public function updateUser(array $config = []);
}
