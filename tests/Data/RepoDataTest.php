<?php

namespace App\Tests\Data;

use App\Data\RepoData;
use PHPUnit\Framework\TestCase;

class RepoDataTest extends TestCase
{
    public function test(): void
    {
        $data = new RepoData('foo/bar', [
            'watchers' => 10,
            'stars' => 20,
            'forks' => 30,
            'pullsOpen' => 40,
            'pullsClosed' => 50,
        ]);

        $this->assertSame('foo/bar', $data->getFullName());
        $this->assertSame(10, $data->getWatchersCount());
        $this->assertSame(20, $data->getStarsCount());
        $this->assertSame(30, $data->getForksCount());
        $this->assertSame(40, $data->getPullsOpenCount());
        $this->assertSame(50, $data->getPullsClosedCount());

        $this->assertSame([
            'full_name' => 'foo/bar',
            'watchers' => 10,
            'stars' => 20,
            'forks' => 30,
            'pulls_open' => 40,
            'pulls_closed' => 50,
        ], $data->jsonSerialize());
    }

    /**
     * @dataProvider dataException
     */
    public function testException(string $fullName, array $stats, string $exception): void
    {
        $this->expectException($exception);

        new RepoData($fullName, $stats);
    }

    public function dataException(): \Generator
    {
        yield 'empty name; empty stats' => ['', [], \InvalidArgumentException::class];

        yield 'good name; empty stats' => ['foo/bar', [], \InvalidArgumentException::class];

        yield 'good name; invalid stats' => ['foo/bar', [
            'watchers' => 10,
            'stars' => 20,
            'forks' => 30,
            'pulls_open' => 40,
            'pulls_closed' => 50,
        ], \InvalidArgumentException::class];

        yield 'empty name; valid stats' => ['', [
            'watchers' => 10,
            'stars' => 20,
            'forks' => 30,
            'pullsOpen' => 40,
            'pullsClosed' => 50,
        ], \InvalidArgumentException::class];
    }
}
