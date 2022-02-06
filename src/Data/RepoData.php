<?php

namespace App\Data;

class RepoData
{

    protected int $watchers;
    protected int $stars;
    protected int $forks;
    protected int $pullsOpen;
    protected int $pullsClosed;

    public function __construct(int $watchers, int $stars, int $forks, int $pullsOpen, int $pullsClosed)
    {
        $this->watchers = $watchers;
        $this->stars = $stars;
        $this->forks = $forks;
        $this->pullsOpen = $pullsOpen;
        $this->pullsClosed = $pullsClosed;
    }

    public function getWatchersCount(): int
    {
        return $this->watchers;
    }

    public function getStarsCount(): int
    {
        return $this->stars;
    }

    public function getForksCount(): int
    {
        return $this->forks;
    }

    public function getPullsOpenCount(): int
    {
        return $this->pullsOpen;
    }

    public function getPullsClosedCount(): int
    {
        return $this->pullsClosed;
    }
}
