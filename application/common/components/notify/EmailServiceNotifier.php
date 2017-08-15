<?php
namespace Sil\Idp\IdSync\common\components\notify;

use Sil\EmailService\Client\EmailServiceClient;
use Sil\Idp\IdSync\common\interfaces\NotifierInterface;
use Sil\Idp\IdSync\common\models\User;

/**
 * NOTE: If you add public methods to this class, first add them to the
 *       NotifierInterface.
 */
class EmailServiceNotifier implements NotifierInterface
{
    /** @var array */
    protected $emailServiceConfig;
    
    /** @var string */
    protected $organizationName;
    
    /** @var string */
    protected $toEmailAddress;
    
    /**
     * Constructor.
     *
     * @param string $toEmailAddress What address to send the email to.
     * @param string $organizationName The name of the organization.
     * @param array $emailServiceConfig The array of configuration values for
     *     the email service client.
     */
    public function __construct(
        string $toEmailAddress,
        string $organizationName,
        array $emailServiceConfig
    ) {
        $this->toEmailAddress = $toEmailAddress;
        $this->organizationName = $organizationName;
        $this->emailServiceConfig = $emailServiceConfig;
    }
    
    /**
     * @return EmailServiceClient
     */
    protected function getEmailServiceClient()
    {
        $config = $this->emailServiceConfig;
        return new EmailServiceClient(
            $config['baseUrl'],
            $config['accessToken'],
            [
                EmailServiceClient::ASSERT_VALID_IP_CONFIG => $config['assertValidIp'],
                EmailServiceClient::TRUSTED_IPS_CONFIG => $config['validIpRanges'],
            ]
        );
    }
    
    /**
     * {@inheritdoc}
     */
    public function sendMissingEmailNotice(array $users)
    {
        $htmlBody = \Yii::$app->view->render('@common/mail/missing-email.html.php', [
            'idStoreName' => $this->idStoreName,
            'organizationName' => $this->organizationName,
            'users' => $users,
        ]);
        $textBody = \Yii::$app->view->render('@common/mail/missing-email.text.php', [
            'idStoreName' => $this->idStoreName,
            'organizationName' => $this->organizationName,
            'users' => $users,
        ]);
        
        $numUsers = count($users);
        $this->getEmailServiceClient()->email([
            'to_address' => $this->toEmailAddress,
            'subject' => sprintf(
                'Email address missing for %s %s user%s',
                $numUsers,
                $this->organizationName,
                ($numUsers === 1 ? '' : 's')
            ),
            'html_body' => $htmlBody,
            'text_body' => $textBody,
        ]);
    }
}
