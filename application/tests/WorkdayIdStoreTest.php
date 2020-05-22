<?php

namespace Sil\Idp\IdSync\tests;

use Sil\Idp\IdSync\common\components\adapters\WorkdayIdStore;
use PHPUnit\Framework\TestCase;

class WorkdayIdStoreTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
        require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
    }

    public function testGetIdStoreName()
    {
        $idStore = $this->getWorkdayIdStore();
        $this->assertEquals('Workday', $idStore->getIdStoreName());
    }

    public function testGenerateGroupsListsDefault()
    {
        $idStore = $this->getWorkdayIdStore();
        $users = [
            [
                'company_ids' => 'a b c',
                'ou_tree' => 'd e f',
            ],
        ];
        $idStore->generateGroupsLists($users);
        $this->assertEquals('a,b,c,d,e,f', $users[0]['Groups']);
    }

    public function testGenerateGroupsListsCustom()
    {
        $idStore = $this->getWorkdayIdStore([
            'groupsFields' => 'field1,field2'
        ]);
        $users = [
            [
                'field1' => 'x y z',
                'field2' => '1 2 3',
            ],
        ];
        $idStore->generateGroupsLists($users);
        $this->assertEquals('x,y,z,1,2,3', $users[0]['Groups']);
    }

    public function testGenerateGroupsListsMissingCompanyIDs()
    {
        $idStore = $this->getWorkdayIdStore();
        $users = [
            [
                'ou_tree' => 'd e f',
            ],
        ];
        $idStore->generateGroupsLists($users);
        $this->assertEquals('d,e,f', $users[0]['Groups']);
    }

    public function testGenerateGroupsListsMissingOUTree()
    {
        $idStore = $this->getWorkdayIdStore();
        $users = [
            [
                'company_ids' => 'a b c',
            ],
        ];
        $idStore->generateGroupsLists($users);
        $this->assertEquals('a,b,c', $users[0]['Groups']);
    }

    private function getWorkdayIdStore($config = []): WorkdayIdStore
    {
        return new WorkdayIdStore(array_merge($config, [
            'apiUrl' => 'https://workday.example.com',
            'username' => 'username',
            'password' => 'password',
        ]));
    }
}
