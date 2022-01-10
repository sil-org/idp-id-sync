<?php
namespace Sil\Idp\IdSync\common\components\notify;

use Sil\Idp\IdSync\common\interfaces\NotifierInterface;

class FakeEmailNotifier implements NotifierInterface
{
    /* @var array */
    public $emailsSent = [];

    public function forgetFakeEmailsSent()
    {
        $this->emailsSent = [];
    }

    public function getSiteStatus(): string
    {
        return "OK";
    }

    public function sendMissingEmailNotice(array $users)
    {
        $numUsers = count($users);
        $this->emailsSent[] = [
            'to_address' => 'fake@example.com',
            'subject' => sprintf(
                'Email address missing for %s orgName user%s',
                $numUsers,
                ($numUsers === 1 ? '' : 's')
            ),
            'html_body' => 'This is the html body',
            'text_body' => 'This is the text body',
        ];
    }
}
