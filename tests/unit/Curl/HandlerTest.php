<?php
/**
 * This file is part of Swoole.
 *
 * @link     https://www.swoole.com
 * @contact  team@swoole.com
 * @license  https://github.com/swoole/library/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Swoole\Curl;

use PHPUnit\Framework\TestCase;
use Swoole\Coroutine;
use Swoole\Tests\HookFlagsTrait;

/**
 * Class HandlerTest
 *
 * @internal
 * @coversNothing
 * @runTestsInSeparateProcesses
 */
class HandlerTest extends TestCase
{
    use HookFlagsTrait;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::saveHookFlags();
    }

    public static function tearDownAfterClass(): void
    {
        self::restoreHookFlags();
        parent::tearDownAfterClass();
    }

    public function setUp(): void
    {
        parent::setUp();
        self::setHookFlags(SWOOLE_HOOK_CURL);
    }

    /**
     * @covers \Swoole\Curl\Handler::execute()
     */
    public function testRedirect()
    {
        Coroutine\run(function () {
            $ch = curl_init('http://alturl.com/6xb2v');
            self::assertInstanceOf(Handler::class, $ch, 'Variable $ch should be a Handler object instead of a curl resource');

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            self::assertEquals(200, $httpCode, 'HTTP status code should be 200 instead of 301');
        });
    }

    /**
     * @covers \Swoole\Curl\Handler::__toString()
     */
    public function testToString()
    {
        Coroutine\run(function () {
            $ch = curl_init();
            self::assertRegExp('/Object\(\w+\) of type \(curl\)/', (string) $ch);
        });
    }

    /**
     * @covers \Swoole\Curl\Handler::execute()
     */
    public function testCustomHost()
    {
        Coroutine\run(function () {
            $ip = Coroutine::gethostbyname('httpbin.org');
            $ch = curl_init("http://{$ip}/get");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Host: httpbin.org']);
            $body = curl_exec($ch);
            $body = json_decode($body, true);
            self::assertSame($body['headers']['Host'], 'httpbin.org');
            curl_close($ch);
        });
    }

    /**
     * @covers \Swoole\Curl\Handler::execute()
     */
    public function testHeaderName()
    {
        Coroutine\run(function () {
            $ch = curl_init('http://httpbin.org/get');
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = substr($response, 0, $headerSize);
            $this->assertStringContainsStringIgnoringCase("\nDate:", $headers);
            $this->assertStringContainsStringIgnoringCase("\nContent-Type:", $headers);
            $this->assertStringContainsStringIgnoringCase("\nContent-Length:", $headers);
            curl_close($ch);
        });
    }

    /**
     * @covers \Swoole\Curl\Handler::execute()
     */
    public function testWriteFunction()
    {
        Coroutine\run(function () {
            $url = 'https://httpbin.org/get';
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) {
                self::assertIsString($data);
                $body = json_decode($data, true);
                self::assertSame($body['headers']['Host'], 'httpbin.org');
                return strlen($data);
            });

            $res = curl_exec($ch);
            curl_close($ch);
            self::assertTrue($res);
        });
    }

    /**
     * @covers \Swoole\Curl\Handler::execute()
     */
    public function testResolve()
    {
        Coroutine\run(function () {
            $host = 'httpbin.org';
            $url = 'https://httpbin.org/get';
            $ip = Coroutine::gethostbyname($host);
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_RESOLVE, ["{$host}:443:{$ip}"]);

            $data = curl_exec($ch);
            $httpPrimaryIp = curl_getinfo($ch, CURLINFO_PRIMARY_IP);
            curl_close($ch);
            $body = json_decode($data, true);
            self::assertSame($body['headers']['Host'], 'httpbin.org');
            self::assertEquals($body['url'], $url);
            self::assertEquals($ip, $httpPrimaryIp);
        });
    }

    /**
     * @covers \Swoole\Curl\Handler::execute()
     */
    public function testInvalidResolve()
    {
        Coroutine\run(function () {
            $host = 'httpbin.org';
            $url = 'https://httpbin.org/get';
            $ip = '127.0.0.1'; // An incorrect IP in use.
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_RESOLVE, ["{$host}:443:{$ip}"]);

            $body = curl_exec($ch);
            $httpPrimaryIp = curl_getinfo($ch, CURLINFO_PRIMARY_IP);
            curl_close($ch);
            self::assertFalse($body);
            self::assertSame('', $httpPrimaryIp);
        });
    }

    /**
     * @covers \Swoole\Curl\Handler::execute()
     */
    public function testResolve2()
    {
        Coroutine\run(function () {
            $host = 'httpbin.org';
            $url = 'https://httpbin.org/get';
            $ip = Coroutine::gethostbyname($host);
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_RESOLVE, ["{$host}:443:127.0.0.1", "{$host}:443:{$ip}"]);

            $data = curl_exec($ch);
            $httpPrimaryIp = curl_getinfo($ch, CURLINFO_PRIMARY_IP);
            $body = json_decode($data, true);
            self::assertSame($body['headers']['Host'], 'httpbin.org');
            self::assertEquals($body['url'], $url);
            self::assertEquals($ip, $httpPrimaryIp);
        });
    }

    /**
     * @covers \Swoole\Curl\Handler::execute()
     */
    public function testInvalidResolve2()
    {
        Coroutine\run(function () {
            $host = 'httpbin.org';
            $url = 'https://httpbin.org/get';
            $ip = Coroutine::gethostbyname($host);
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_RESOLVE, ["{$host}:443:{$ip}", "+{$host}:443:127.0.0.1"]);

            $body = curl_exec($ch);
            $httpPrimaryIp = curl_getinfo($ch, CURLINFO_PRIMARY_IP);
            curl_close($ch);
            self::assertFalse($body);
            self::assertSame('', $httpPrimaryIp);
        });
    }

    /**
     * @covers \Swoole\Curl\Handler::execute()
     */
    public function testInvalidResolve3()
    {
        Coroutine\run(function () {
            $host = 'httpbin.org';
            $url = 'https://httpbin.org/get';
            $ip = Coroutine::gethostbyname($host);
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_RESOLVE, ["{$host}:443:{$ip}", "{$host}:443:127.0.0.1"]);

            $body = curl_exec($ch);
            $httpPrimaryIp = curl_getinfo($ch, CURLINFO_PRIMARY_IP);
            curl_close($ch);
            self::assertFalse($body);
            self::assertSame('', $httpPrimaryIp);
        });
    }

    /**
     * @covers \Swoole\Curl\Handler::execute()
     */
    public function testResolve3()
    {
        Coroutine\run(function () {
            $host = 'httpbin.org';
            $url = 'https://httpbin.org/get';
            $ip = Coroutine::gethostbyname($host);
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_RESOLVE, ["{$host}:443:{$ip}", "{$host}:443:127.0.0.1", "-{$host}:443:127.0.0.1"]);

            $data = curl_exec($ch);
            $httpPrimaryIp = curl_getinfo($ch, CURLINFO_PRIMARY_IP);
            $body = json_decode($data, true);
            self::assertSame($body['headers']['Host'], 'httpbin.org');
            self::assertEquals($body['url'], $url);
            self::assertSame('', $httpPrimaryIp);
        });
    }

    public function testOptPrivate()
    {
        Coroutine\run(function () {
            $url = 'https://httpbin.org/get';
            $private = 'swoole';
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_PRIVATE, $private);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Host: httpbin.org']);
            $body = curl_exec($ch);
            $body = json_decode($body, true);
            $get_private = curl_getinfo($ch, CURLINFO_PRIVATE);
            self::assertEquals($private, $get_private);
            self::assertSame($body['headers']['Host'], 'httpbin.org');
            curl_close($ch);
        });
    }
}
