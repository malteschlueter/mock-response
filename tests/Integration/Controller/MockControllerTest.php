<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MockControllerTest extends WebTestCase
{
    use ClockSensitiveTrait;

    /**
     * @dataProvider provideCodes
     */
    public function test_that_status_code_is_always_expected(int $expectedStatusCode): void
    {
        $client = self::createClient();

        $client->request(Request::METHOD_GET, '/status/' . $expectedStatusCode);

        $this->assertResponseStatusCodeSame($expectedStatusCode);
    }

    /**
     * @dataProvider provideCodes
     */
    public function test_that_status_code_is_only_sometimes_as_expected(int $expectedStatusCode): void
    {
        $client = self::createClient();

        for ($attempt = 0; $attempt < 100; ++$attempt) {
            $client->request(Request::METHOD_GET, '/status/' . $expectedStatusCode . '/random');

            if ($client->getResponse()->getStatusCode() !== $expectedStatusCode) {
                continue;
            }

            $this->assertResponseStatusCodeSame($expectedStatusCode);

            break;
        }

        if ($attempt >= 100) {
            $this->fail('Failed asserting that the response status code is ' . $expectedStatusCode);
        }
    }

    /**
     * @dataProvider provideCodes
     */
    public function test_that_status_code_is_only_by_interval_as_expected(int $expectedStatusCode): void
    {
        $client = self::createClient();

        self::mockTime(new \DateTimeImmutable('2024-06-18 15:31:00'));

        $client->request(Request::METHOD_GET, '/status/' . $expectedStatusCode . '/interval/minute/5');

        $this->assertResponseStatusCodeSame($expectedStatusCode);
    }

    /**
     * @dataProvider provideCodes
     */
    public function test_that_the_status_code_is_beside_the_interval_always_200(int $expectedStatusCode): void
    {
        $client = self::createClient();

        self::mockTime(new \DateTimeImmutable('2024-06-18 15:29:00'));

        $client->request(Request::METHOD_GET, '/status/' . $expectedStatusCode . '/interval/minute/5');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function provideCodes(): iterable
    {
        foreach (Response::$statusTexts as $code => $text) {
            yield 'Status: ' . $code => [$code];
        }
    }
}
