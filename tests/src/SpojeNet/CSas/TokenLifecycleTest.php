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

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use PHPUnit\Framework\TestCase;
use SpojeNet\CSas\Application;
use SpojeNet\CSas\Auth;
use SpojeNet\CSas\Token;

/**
 * Refresh failure handling, revocation and notification throttling.
 */
class TokenLifecycleTest extends TestCase
{
    public function testInvalidGrantRejectsRefreshToken(): void
    {
        $this->assertTrue(Token::isRefreshTokenRejected(['error' => 'invalid_grant'], 'whatever'));
        $this->assertTrue(Token::isRefreshTokenRejected(['error' => 'request_error', 'error_code' => '7109'], 'x'));
        $this->assertTrue(Token::isRefreshTokenRejected([], 'Refresh token has expired'));
    }

    public function testTransientErrorKeepsRefreshToken(): void
    {
        $this->assertFalse(Token::isRefreshTokenRejected(['error' => 'server_error'], 'Internal error'));
        $this->assertFalse(Token::isRefreshTokenRejected(['error' => 'temporarily_unavailable'], 'Try later'));
    }

    public function testTransientErrorDoesNotClearRefreshToken(): void
    {
        $token = $this->getMockBuilder(Token::class)->onlyMethods(['dbSync', 'addStatusMessage'])->getMock();
        $token->expects($this->never())->method('dbSync');
        $token->setDataValue('refresh_token', 'still_good');

        $provider = $this->createStub(Auth::class);
        $provider->method('getAccessToken')->willThrowException(
            new IdentityProviderException('server_error', 0, ['error' => 'server_error', 'error_description' => 'IDP down']),
        );

        try {
            $token->refreshToken($provider);
            $this->fail('RuntimeException expected');
        } catch (\RuntimeException $exception) {
            $this->assertSame(25, $exception->getCode());
        }

        $this->assertSame('still_good', $token->getDataValue('refresh_token'));
    }

    public function testRevokeTokenPostsToRevokeEndpoint(): void
    {
        $history = [];
        $stack = HandlerStack::create(new MockHandler([new Response(200)]));
        $stack->push(Middleware::history($history));

        $app = $this->createStub(Application::class);
        $app->method('sandboxMode')->willReturn(false);
        $app->method('getClientId')->willReturn('client-1');

        $auth = new Auth($app);
        $auth->setHttpClient(new Client(['handler' => $stack]));
        $auth->revokeToken('rt-123');

        $this->assertCount(1, $history);
        $request = $history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(Auth::PRODUCTION_SITE.'/revoke', (string) $request->getUri());
        parse_str((string) $request->getBody(), $body);
        $this->assertSame(['token' => 'rt-123', 'token_type_hint' => 'refresh_token', 'client_id' => 'client-1'], $body);
    }

    public function testNotificationDue(): void
    {
        $now = 1_800_000_000;

        $this->assertTrue(Token::notificationDue([], 86400, $now));
        $this->assertTrue(Token::notificationDue(['notified_at' => null], 86400, $now));
        $this->assertFalse(Token::notificationDue(['notified_at' => date('Y-m-d H:i:s', $now - 3600)], 86400, $now));
        $this->assertTrue(Token::notificationDue(['notified_at' => date('Y-m-d H:i:s', $now - 86400)], 86400, $now));
    }
}
