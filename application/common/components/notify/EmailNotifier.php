<?php
namespace Sil\Idp\IdSync\common\components\notify;

use Sil\Idp\IdSync\common\interfaces\NotifierInterface;
use Sil\Idp\IdSync\common\models\User;
use yii\mail\MailerInterface;

/**
 * NOTE: If you add methods to this class, first add them to NotifierInterface.
 */
class EmailNotifier implements NotifierInterface
{
    /** @var string */
    protected $organizationName;
    
    /** @var string */
    protected $idStoreName;
    
    /** @var MailerInterface */
    protected $mailer;
    
    /** @var string */
    protected $toEmailAddress;
    
    /** @var string */
    protected $fromEmailAddress;
    
    /**
     * Constructor.
     *
     * @param MailerInterface $mailer The Mailer to use for actually sending
     *     email.
     * @param string $toEmailAddress What address to send the email to.
     * @param string $fromEmailAddress What address to send the email from.
     * @param string $organizationName The name of the organization.
     * @param string $idStoreName The name of the ID Store.
     */
    public function __construct(
        MailerInterface $mailer,
        string $toEmailAddress,
        string $fromEmailAddress,
        string $organizationName,
        string $idStoreName
    ) {
        $this->mailer = $mailer;
        $this->toEmailAddress = $toEmailAddress;
        $this->fromEmailAddress = $fromEmailAddress;
        $this->organizationName = $organizationName;
        $this->idStoreName = $idStoreName;
    }
    
    public function sendMissingEmailNotice(User $user)
    {
        $message = $this->mailer->compose('@common/mail/missing-email', [
            'idStoreName' => $this->idStoreName,
            'organizationName' => $this->organizationName,
            'employeeId' => $user->employeeId,
            'username' => $user->username,
            'firstName' => $user->firstName,
            'lastName' => $user->lastName,
        ]);
        $message->setTo($this->toEmailAddress);
        $message->setFrom($this->fromEmailAddress);
        $message->setSubject('Email address missing for ' . $user->username);
        $isSuccessful = $message->send();
        if ( ! $isSuccessful) {
            throw new \Exception(sprintf(
                'Failed to send notification email (%s) from %s to %s.',
                $message->getSubject(),
                var_export($message->getFrom(), true),
                var_export($message->getTo(), true)
            ));
        }
    }
}
