<?php declare(strict_types=1);

namespace App\Data;

use Webmozart\Assert\Assert;

class RepoData
{

    protected string $fullName;
    protected array $stats;

    public function __construct(string $fullName, array $stats)
    {
        Assert::allInteger($stats);
        Assert::keyExists($stats, 'watchers');
        Assert::keyExists($stats, 'stars');
        Assert::keyExists($stats, 'forks');
        Assert::keyExists($stats, 'pullsOpen');
        Assert::keyExists($stats, 'pullsOpen');

        $this->stats = $stats;
        $this->fullName = $fullName;
    }

    public function getWatchersCount(): int
    {
        return $this->stats['watchers'];
    }

    public function getStarsCount(): int
    {
        return $this->stats['stars'];
    }

    public function getForksCount(): int
    {
        return $this->stats['forks'];
    }

    public function getPullsOpenCount(): int
    {
        return $this->stats['pullsOpen'];
    }

    public function getPullsClosedCount(): int
    {
        return $this->stats['pullsClosed'];
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }
}
