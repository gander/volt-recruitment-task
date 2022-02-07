<?php

namespace App\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class RepoDataProviderTest extends TestCase
{
    /**
     * @dataProvider dataProviderGetData
     */
    public function testGetData(MockResponse $first, MockResponse $second): void
    {
        $httpClient = new MockHttpClient([$first, $second]);

        $provider = new \App\Provider\RepoDataProvider($httpClient);

        $data = $provider->getData('symfony', 'symfony');

        $this->assertIsObject($data);

        $this->assertIsString($data->getFullName());
        $this->assertIsInt($data->getWatchersCount());
        $this->assertIsInt($data->getStarsCount());
        $this->assertIsInt($data->getForksCount());
        $this->assertIsInt($data->getPullsOpenCount());
        $this->assertIsInt($data->getPullsClosedCount());

        $this->assertSame("symfony/symfony", $data->getFullName());
        $this->assertGreaterThanOrEqual(0, $data->getWatchersCount());
        $this->assertGreaterThanOrEqual(0, $data->getStarsCount());
        $this->assertGreaterThanOrEqual(0, $data->getForksCount());
        $this->assertGreaterThanOrEqual(0, $data->getPullsOpenCount());
        $this->assertGreaterThanOrEqual(0, $data->getPullsClosedCount());

    }

    public function dataProviderGetData(): \Generator
    {
        yield 'good json; empty json' => [
            new MockResponse('{"full_name":"symfony/symfony","subscribers_count":0,"stargazers_count":0,"forks_count":0}'),
            new MockResponse('[]'),
        ];

        yield 'good json; good json: both states' => [
            new MockResponse('{"full_name":"symfony/symfony","subscribers_count":0,"stargazers_count":0,"forks_count":0}'),
            new MockResponse('[{"state":"open"},{"state":"closed"}]'),
        ];

        yield 'good json; good json: state open' => [
            new MockResponse('{"full_name":"symfony/symfony","subscribers_count":1,"stargazers_count":2,"forks_count":3}'),
            new MockResponse('[{"state":"open"}]'),
        ];

        yield 'good json; good json: state closed' => [
            new MockResponse('{"full_name":"symfony/symfony","subscribers_count":3,"stargazers_count":2,"forks_count":1}'),
            new MockResponse('[{"state":"closed"}]'),
        ];
    }


    /**
     * @dataProvider dataProviderGetDataExceptions
     */
    public function testGetDataExceptions(MockResponse $first, MockResponse $second, string $exception): void
    {
        $httpClient = new MockHttpClient([$first, $second]);

        $provider = new \App\Provider\RepoDataProvider($httpClient);

        $this->expectException($exception);

        $provider->getData('symfony', 'symfony');
    }

    public function dataProviderGetDataExceptions(): \Generator
    {
        yield 'empty bodies' => [
            new MockResponse(''),
            new MockResponse(''),
            \JsonException::class,
        ];

        yield 'empty body, empty json' => [
            new MockResponse(''),
            new MockResponse('{}'),
            \JsonException::class,
        ];

        yield 'empty jsons' => [
            new MockResponse('{}'),
            new MockResponse('{}'),
            \InvalidArgumentException::class,
        ];

        yield 'missing key in pulls' => [
            new MockResponse('{"full_name":"symfony/symfony","subscribers_count":1,"stargazers_count":2,"forks_count":3}'),
            new MockResponse('[{}]'),
            \InvalidArgumentException::class,
        ];

        yield 'good json; bad json: state other' => [
            new MockResponse('{"full_name":"symfony/symfony","subscribers_count":3,"stargazers_count":2,"forks_count":1}'),
            new MockResponse('[{"state":"other"}]'),
            \InvalidArgumentException::class,
        ];


        yield 'bad json: wrong name; good json' => [
            new MockResponse('{"full_name":"laravel/laravel","subscribers_count":0,"stargazers_count":0,"forks_count":0}'),
            new MockResponse('[{"state":"open"},{"state":"closed"}]'),
            \InvalidArgumentException::class,
        ];

    }

    public function testGetDataWrongOwnerException(): void
    {
        $httpClient = new MockHttpClient();
        $provider = new \App\Provider\RepoDataProvider($httpClient);

        $this->expectException(\InvalidArgumentException::class);

        $provider->getData('', 'symfony');
    }

    public function testGetDataWrongNameException(): void
    {
        $httpClient = new MockHttpClient();
        $provider = new \App\Provider\RepoDataProvider($httpClient);

        $this->expectException(\InvalidArgumentException::class);

        $provider->getData('symfony', '');
    }

}
