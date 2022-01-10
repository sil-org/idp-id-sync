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
            $outputLine = sprintf('%s. Employee ID %s', ++$counter, $user->getEmployeeId());
            if ($user->getUsername() !== null) {
                $outputLine .= sprintf(' (%s)', $user->getUsername());
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

    public function sendNewUserNotice(User $user)
    {
        $userInfo = sprintf('Employee ID %s', $user->getEmployeeId());
        if ($user->getUsername() !== null) {
            $userInfo .= sprintf(' (%s)', $user->getUsername());
        }
        $hr = $user->getHRContactEmail();

        echo sprintf("NOTIFIER: Notify %s that the following user was created: \n%s", $hr, $userInfo) . PHP_EOL;
    }
}
