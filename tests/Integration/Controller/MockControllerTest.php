<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MockControllerTest extends WebTestCase
{
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

    public function provideCodes(): iterable
    {
        foreach (Response::$statusTexts as $code => $text) {
            yield 'Status: ' . $code => [$code];
        }
    }
}
