<?php

namespace Sil\Idp\IdSync\common\components\exceptions;

use Exception;

/**
 * Thrown when a notification can't be sent because there is no email address
 * to send it to (no HR contact email for the user and no fallback configured).
 */
class MissingNotifierEmailException extends Exception
{
}
