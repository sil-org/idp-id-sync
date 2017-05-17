<?php
namespace Sil\Idp\IdSync\common\components\notify;

use Sil\Idp\IdSync\common\interfaces\NotifierInterface;
use Sil\Idp\IdSync\common\models\User;

/**
 * NOTE: If you add methods to this class, first add them to NotifierInterface.
 */
class ConsoleNotifier implements NotifierInterface
{
    /**
     * Get a plain text string that is a list identifying the given Users.
     *
     * @param User[] $users
     * @return string
     */
    protected function getBasicInfoAsTextList($users)
    {
        $counter = 0;
        $outputLines = [];
        foreach ($users as $user) {
            $outputLines[] = sprintf(
                '%s. %s, %s',
                ++$counter,
                $user->username,
                $user->employeeId
            );
        }        
        return join("\n", $outputLines) . "\n";
    }
    
    /**
     * {@inheritdoc}
     */
    public function sendMissingEmailNotice(array $users)
    {
        echo sprintf(
            "NOTIFIER: The following %s users lack an email address: \n%s",
            count($users),
            $this->getBasicInfoAsTextList($users)
        ) . PHP_EOL;
    }
}
