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
            $outputLine = sprintf('%s. Employee ID %s', ++$counter, $user->employeeId);
            if ($user->username !== null) {
                $outputLine .= sprintf(' (%s)', $user->username);
            }
            $outputLines[] = $outputLine;
        }
        return join("\n", $outputLines) . "\n";
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSiteStatus(): string
    {
        return 'No external service status to check.';
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
