<?php

namespace App\Tests\Processor;

use App\Data\RepoData;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class RepoDataProcessorTest extends TestCase
{
    /**
     * @dataProvider dataRepoDataProcessor
     */
    public function testCompareRepos(string $repo1, string $repo2, $responseFactory, array $stats, array $diffs): void
    {
        $httpClient = new MockHttpClient($responseFactory);

        $provider = new \App\Provider\RepoDataProvider($httpClient);
        $processor = new \App\Processor\RepoDataProcessor($provider);

        $result = $processor->compareRepos($repo1, $repo2);

        $this->assertIsObject($result);

        $this->assertIsArray($result->getStats());
        $this->assertCount(2, $result->getStats());
        $this->assertContainsOnlyInstancesOf(RepoData::class, $result->getStats());

        [$stats1, $stats2] = $stats;
        [$data1, $data2] = $result->getStats();

        $this->assertEquals($stats1, $data1->jsonSerialize());
        $this->assertEquals($stats2, $data2->jsonSerialize());

        $this->assertIsArray($result->getDiffs());
        $this->assertCount(2, $result->getDiffs());
        $this->assertContainsOnlyInstancesOf(RepoData::class, $result->getDiffs());

        [$diffs1, $diffs2] = $diffs;
        [$data1, $data2] = $result->getDiffs();

        $this->assertEquals($diffs1, $data1->jsonSerialize());
        $this->assertEquals($diffs2, $data2->jsonSerialize());

        $this->assertEquals(compact('stats', 'diffs'), $result->jsonSerialize());
    }

    public function dataRepoDataProcessor(): \Generator
    {
        yield [
            'https://github.com/symfony/symfony',
            'https://github.com/laravel/laravel',
            [
                new MockResponse('{"full_name":"symfony/symfony","subscribers_count":1,"stargazers_count":2,"forks_count":3}'),
                new MockResponse('[{"state":"open"},{"state":"closed"}]'),
                new MockResponse('{"full_name":"laravel/laravel","subscribers_count":3,"stargazers_count":2,"forks_count":1}'),
                new MockResponse('[{"state":"open"},{"state":"closed"}]'),
            ], [
                [
                    'full_name' => 'symfony/symfony',
                    'watchers' => 1,
                    'stars' => 2,
                    'forks' => 3,
                    'pulls_open' => 1,
                    'pulls_closed' => 1,
                ],
                [
                    'full_name' => 'laravel/laravel',
                    'watchers' => 3,
                    'stars' => 2,
                    'forks' => 1,
                    'pulls_open' => 1,
                    'pulls_closed' => 1,
                ],
            ], [
                [
                    'full_name' => 'symfony/symfony',
                    'watchers' => -1,
                    'stars' => 0,
                    'forks' => 1,
                    'pulls_open' => 0,
                    'pulls_closed' => 0,
                ],
                [
                    'full_name' => 'laravel/laravel',
                    'watchers' => 1,
                    'stars' => 0,
                    'forks' => -1,
                    'pulls_open' => 0,
                    'pulls_closed' => 0,
                ],
            ]];


        yield [
            'symfony/symfony',
            'laravel/laravel',
            [
                new MockResponse('{"full_name":"symfony/symfony","subscribers_count":40,"stargazers_count":50,"forks_count":60}'),
                new MockResponse('[{"state":"open"},{"state":"closed"},{"state":"open"}]'),
                new MockResponse('{"full_name":"laravel/laravel","subscribers_count":30,"stargazers_count":70,"forks_count":60}'),
                new MockResponse('[{"state":"open"},{"state":"closed"},{"state":"closed"}]'),
            ], [
                [
                    'full_name' => 'symfony/symfony',
                    'watchers' => 40,
                    'stars' => 50,
                    'forks' => 60,
                    'pulls_open' => 2,
                    'pulls_closed' => 1,
                ],
                [
                    'full_name' => 'laravel/laravel',
                    'watchers' => 30,
                    'stars' => 70,
                    'forks' => 60,
                    'pulls_open' => 1,
                    'pulls_closed' => 2,
                ],
            ], [
                [
                    'full_name' => 'symfony/symfony',
                    'watchers' => 1,
                    'stars' => -1,
                    'forks' => 0,
                    'pulls_open' => 1,
                    'pulls_closed' => -1,
                ],
                [
                    'full_name' => 'laravel/laravel',
                    'watchers' => -1,
                    'stars' => 1,
                    'forks' => 0,
                    'pulls_open' => -1,
                    'pulls_closed' => 1,
                ],
            ]];
    }

    /**
     * @dataProvider dataCompareRepoException
     */
    public function testCompareRepoException(string $repo1, string $repo2, string $exception): void
    {
        $httpClient = new MockHttpClient();
        $provider = new \App\Provider\RepoDataProvider($httpClient);
        $processor = new \App\Processor\RepoDataProcessor($provider);

        $this->expectException($exception);

        $processor->compareRepos($repo1, $repo2);
    }

    public function dataCompareRepoException(): array
    {
        return [
            ['', '', \InvalidArgumentException::class],
            ['foo/bar', '', \InvalidArgumentException::class],
            ['foo/', '', \InvalidArgumentException::class],
            ['/bar', '', \InvalidArgumentException::class],
            ['', 'foo/bar', \InvalidArgumentException::class],
            ['', 'foo/', \InvalidArgumentException::class],
            ['', '/bar', \InvalidArgumentException::class],
            ['https://google.com/foo/bar', 'https://google.com/lorem/ipsum', \InvalidArgumentException::class],
        ];
    }


    /**
     * @dataProvider dataGetFullName
     */
    public function testGetFullName(string $url, string $expected)
    {
        $httpClient = new MockHttpClient();
        $provider = new \App\Provider\RepoDataProvider($httpClient);
        $processor = new \App\Processor\RepoDataProcessor($provider);
        $fullName = $processor->getFullName($url);

        $this->assertSame($expected, $fullName);
    }

    public function dataGetFullName(): array
    {
        return [
            ['foo/bar', 'foo/bar'],
            ['lo-r3m/ips_um', 'lo-r3m/ips_um'],
            ['foo/bar/', 'foo/bar'],
            ['https://github.com/foo/bar', 'foo/bar'],
            ['https://github.com/foo/bar/', 'foo/bar'],
            ['https://github.com/lo-r3m/ips_um/', 'lo-r3m/ips_um'],
            ['http://github.com/foo/bar', 'foo/bar'],
            ['http://github.com/foo/bar/', 'foo/bar'],
        ];
    }

    /**
     * @dataProvider dataGetFullNameException
     */
    public function testGetFullNameException(string $url)
    {
        $httpClient = new MockHttpClient();
        $provider = new \App\Provider\RepoDataProvider($httpClient);
        $processor = new \App\Processor\RepoDataProcessor($provider);

        $this->expectException(\InvalidArgumentException::class);

        $processor->getFullName($url);

    }

    public function dataGetFullNameException(): array
    {
        return [
            [''],
            ['/'],
            ['foo/'],
            ['/bar'],
            ['https://google.com'],
            ['https://google.com/foo/bar'],
        ];
    }


}
