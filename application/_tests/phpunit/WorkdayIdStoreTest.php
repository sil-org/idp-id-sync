<?php

namespace Sil\Idp\IdSync\_tests\phpunit;

use Sil\Idp\IdSync\common\components\adapters\WorkdayIdStore;
use PHPUnit\Framework\TestCase;

class WorkdayIdStoreTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
        require_once __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
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

    public function testGenerateGroupsListsDeduplicates()
    {
        $idStore = $this->getWorkdayIdStore();
        $users = [
            [
                'company_ids' => 'a b a c',
                'ou_tree' => 'b d e d',
            ],
        ];
        $idStore->generateGroupsLists($users);
        $this->assertEquals('a,b,c,d,e', $users[0]['Groups']);
    }

    public function testGenerateGroupsListsDropsTrailingGroupsOver255Chars()
    {
        $idStore = $this->getWorkdayIdStore();
        // Each group is 50 'a's + index (51 chars).
        // 4 groups with 3 commas = 4 * 51 + 3 = 207 chars (<= 255)
        // 5 groups with 4 commas = 5 * 51 + 4 = 259 chars (> 255)
        $g1 = str_repeat('a', 50) . '1';
        $g2 = str_repeat('b', 50) . '2';
        $g3 = str_repeat('c', 50) . '3';
        $g4 = str_repeat('d', 50) . '4';
        $g5 = str_repeat('e', 50) . '5';

        $users = [
            [
                'company_ids' => "$g1 $g2",
                'ou_tree' => "$g3 $g4 $g5",
            ],
        ];
        $idStore->generateGroupsLists($users);
        $expected = implode(',', [$g1, $g2, $g3, $g4]);
        $this->assertLessThanOrEqual(WorkdayIdStore::MAX_GROUPS_LENGTH, strlen($users[0]['Groups']));
        $this->assertEquals($expected, $users[0]['Groups']);
    }

    public function testGenerateGroupsListsExact255CharsKept()
    {
        $idStore = $this->getWorkdayIdStore();
        // 250 chars + ',' + 4 chars = 255 chars
        $g1 = str_repeat('a', 250);
        $g2 = 'bcde';

        $users = [
            [
                'company_ids' => $g1,
                'ou_tree' => $g2,
            ],
        ];
        $idStore->generateGroupsLists($users);
        $this->assertEquals(255, strlen($users[0]['Groups']));
        $this->assertEquals("$g1,$g2", $users[0]['Groups']);
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
