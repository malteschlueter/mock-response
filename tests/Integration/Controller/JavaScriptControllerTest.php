<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Enum\JavaScriptMockType;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

final class JavaScriptControllerTest extends PantherTestCase
{
    use ClockSensitiveTrait;

    #[DataProvider('provideTypes')]
    public function test_that_javascript_type_is_always_expected(string $expectedLogMessage, JavaScriptMockType $javaScriptMockType): void
    {
        $client = self::createPantherClient();

        $client->request(Request::METHOD_GET, '/javascript/' . $javaScriptMockType->value);

        $browserLog = $client->getWebDriver()->manage()->getLog('browser');

        $this->assertCount(1, $browserLog);

        $logEntry = reset($browserLog);

        $this->assertSame($expectedLogMessage, $logEntry['message']);
    }

    public static function provideTypes(): iterable
    {
        yield 'Type: ' . JavaScriptMockType::Error->name => [
            'http://127.0.0.1:9080/javascript/error 9:14 Uncaught Error: This is a mock error response.',
            JavaScriptMockType::Error,
        ];
    }
}
