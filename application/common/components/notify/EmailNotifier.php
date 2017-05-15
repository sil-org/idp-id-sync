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
    protected $idpName;
    
    /** @var string */
    protected $idStoreName;
    
    /** @var MailerInterface */
    protected $mailer;
    
    /**
     * The email address that the recipient(s) of these notices (e.g. - HR) will
     * be given, in case they have questions about a notification.
     *
     * @var string
     */
    protected $ourEmailAddress;
    
    /**
     * Constructor.
     *
     * @param MailerInterface $mailer The Mailer to use for actually sending
     *     email.
     */
    public function __construct(
        MailerInterface $mailer,
        string $idpName,
        string $idStoreName,
        string $ourEmailAddress
    ) {
        $this->mailer = $mailer;
        $this->idpName = $idpName;
        $this->idStoreName = $idStoreName;
        $this->ourEmailAddress = $ourEmailAddress;
    }
    
    public function sendMissingEmailNotice(User $user)
    {
        $this->mailer->compose('missing-email', [
            'idStoreName' => $this->idStoreName,
            'idpName' => $this->idpName,
            'employeeId' => $user->employeeId,
            'username' => $user->username,
            'firstName' => $user->firstName,
            'lastName' => $user->lastName,
            'ourEmailAddress' => $this->ourEmailAddress,
        ]);
    }
}
