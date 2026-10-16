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

namespace SpojeNet\CSas\Tests;

use PHPUnit\Framework\TestCase;
use SpojeNet\CSas\Mailer;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

class MailerTest extends TestCase
{
    public function testDsnFromEaseSmtp(): void
    {
        $this->assertSame(
            'smtp://api%40example.com:p%40ss%2Fword@smtp.office365.com:587',
            Mailer::dsnFromEaseSmtp(['port' => '587', 'starttls' => true, 'auth' => true, 'host' => 'smtp.office365.com', 'username' => 'api@example.com', 'password' => 'p@ss/word']),
        );
        $this->assertSame('smtp://localhost:25', Mailer::dsnFromEaseSmtp(['host' => 'localhost']));
    }

    public function testSendMessage(): void
    {
        $mailer = new Mailer(new NullTransport());

        $this->assertTrue($mailer->sendMessage('test@example.com', 'Subject', 'Body', '<p>Body</p>'));
        $this->assertNull($mailer->lastError);
    }

    public function testFailureIsReported(): void
    {
        $transport = $this->createStub(TransportInterface::class);
        $transport->method('send')->willThrowException(new TransportException('535 5.7.3 Authentication unsuccessful'));
        $mailer = new Mailer($transport);

        $this->assertFalse($mailer->sendMessage('test@example.com', 'Subject', 'Body'));
        $this->assertSame('535 5.7.3 Authentication unsuccessful', $mailer->lastError);
    }

    public function testInvalidRecipientIsReported(): void
    {
        $mailer = new Mailer(new NullTransport());

        $this->assertFalse($mailer->sendMessage('', 'Subject', 'Body'));
        $this->assertNotNull($mailer->lastError);
    }
}
