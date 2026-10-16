<?php

declare(strict_types=1);

/**
 * This file is part of the CSASAuthorize  package
 *
 * https://github.com/Spoje-NET/csas-authorize
 *
 * (c) Spoje.Net IT s.r.o. <https://spojenet.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SpojeNet\CSas;

use Symfony\Component\Mailer\Exception\ExceptionInterface as MailerException;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Exception\ExceptionInterface as MimeException;

/**
 * Send emails using Symfony Mailer.
 *
 * Transport is configured by MAIL_DSN (https://symfony.com/doc/current/mailer.html),
 * the deprecated EASE_SMTP JSON is still understood.
 *
 * @no-named-arguments
 */
class Mailer extends \Ease\Sand
{
    /**
     * Reason of the last failed delivery.
     */
    public ?string $lastError = null;
    private ?TransportInterface $transport;

    public function __construct(?TransportInterface $transport = null)
    {
        $this->transport = $transport;
        $this->setObjectName();
    }

    /**
     * Mail transport DSN: MAIL_DSN, deprecated EASE_SMTP or local sendmail.
     */
    public static function dsn(): string
    {
        $dsn = (string) \Ease\Shared::cfg('MAIL_DSN', '');

        if ($dsn !== '') {
            return $dsn;
        }

        $easeSmtp = json_decode((string) \Ease\Shared::cfg('EASE_SMTP', ''), true);

        if (\is_array($easeSmtp) && !empty($easeSmtp['host'])) {
            return self::dsnFromEaseSmtp($easeSmtp);
        }

        return 'sendmail://default';
    }

    /**
     * Convert deprecated EASE_SMTP (PEAR Mail parameters) to Symfony DSN.
     *
     * STARTTLS is used automatically when offered, port 465 means implicit TLS.
     *
     * @param array<string, mixed> $params
     */
    public static function dsnFromEaseSmtp(array $params): string
    {
        $credentials = '';

        if (!empty($params['auth']) && !empty($params['username'])) {
            $credentials = rawurlencode((string) $params['username']).':'.rawurlencode((string) ($params['password'] ?? '')).'@';
        }

        return 'smtp://'.$credentials.$params['host'].':'.($params['port'] ?? 25);
    }

    /**
     * Sender address: MAIL_FROM, EMAIL_FROM, EASE_FROM or user@hostname.
     */
    public static function from(): string
    {
        return (string) \Ease\Shared::cfg('MAIL_FROM', \Ease\Shared::cfg('EMAIL_FROM', \Ease\Shared::cfg('EASE_FROM', get_current_user().'@'.gethostname())));
    }

    /**
     * Send message, failure reason is kept in $lastError and logged.
     */
    public function sendMessage(string $to, string $subject, string $text, ?string $html = null): bool
    {
        $this->lastError = null;

        try {
            $email = (new Email())
                ->from(self::from())
                ->to($to)
                ->subject($subject)
                ->text($text);

            if ($html !== null) {
                $email->html($html);
            }

            (new SymfonyMailer($this->transport ?? Transport::fromDsn(self::dsn())))->send($email);
        } catch (MailerException|MimeException $exception) {
            $this->lastError = $exception->getMessage();
            $this->addStatusMessage(sprintf(_('Message %s for %s was not sent: %s'), $subject, $to, $this->lastError), 'warning');

            return false;
        }

        $this->addStatusMessage(sprintf(_('Message %s was sent to %s'), $subject, $to), 'success');

        return true;
    }
}
