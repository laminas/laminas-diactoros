<?php

declare(strict_types=1);

namespace LaminasTest\Diactoros;

use Laminas\Diactoros\Uri;
use PHPUnit\Framework\TestCase;

final class UriParsedUserInfoTest extends TestCase
{
    /** Password from a parsed URI should be encoded the same way as via withUserInfo(). */
    public function testParsedPasswordIsEncodedConsistentlyWithWithUserInfo(): void
    {
        $parsed = new Uri('http://user:p%zz^@example.com/');
        $built  = (new Uri('http://example.com/'))->withUserInfo('user', 'p%zz^');

        $this->assertSame($built->getUserInfo(), $parsed->getUserInfo());
    }

    public function testParsedPasswordKeepsExistingPercentEncoding(): void
    {
        $uri = new Uri('http://user:p%40ss@example.com/');

        $this->assertSame('user:p%40ss', $uri->getUserInfo());
    }
}
