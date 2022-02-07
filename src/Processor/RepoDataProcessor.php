<?php

namespace App\Processor;

use App\Data\RepoData;
use App\Provider\RepoDataProvider;
use InvalidArgumentException;
use JsonSerializable;

class RepoDataProcessor
{
    private const URL_REGEXP = '~^(?:https?://github.com/)?(?<full_name>[a-zA-Z0-9-_.]+/[a-zA-Z0-9-_.]+)/?$~';
    protected RepoDataProvider $provider;

    public function __construct(RepoDataProvider $provider)
    {
        $this->provider = $provider;
    }

    public function compareRepos(string $repo1, string $repo2): object
    {
        $fullName1 = $this->getFullName($repo1);
        $fullName2 = $this->getFullName($repo2);

        $result1 = $this->provider->getData($fullName1);
        $result2 = $this->provider->getData($fullName2);

        return new class ($result1, $result2) implements JsonSerializable {
            protected RepoData $data1;
            protected RepoData $data2;

            public function __construct(RepoData $data1, RepoData $data2)
            {
                $this->data1 = $data1;
                $this->data2 = $data2;
            }

            public function getStats(): array
            {
                return [
                    $this->data1,
                    $this->data2,
                ];
            }

            public function getDiffs(): array
            {
                return [
                    new RepoData($this->data1->getFullName(), [
                        'watchers' => $this->data1->getWatchersCount() <=> $this->data2->getWatchersCount(),
                        'stars' => $this->data1->getStarsCount() <=> $this->data2->getStarsCount(),
                        'forks' => $this->data1->getForksCount() <=> $this->data2->getForksCount(),
                        'pullsOpen' => $this->data1->getPullsOpenCount() <=> $this->data2->getPullsOpenCount(),
                        'pullsClosed' => $this->data1->getPullsClosedCount() <=> $this->data2->getPullsClosedCount(),
                    ]),
                    new RepoData($this->data2->getFullName(), [
                        'watchers' => $this->data2->getWatchersCount() <=> $this->data1->getWatchersCount(),
                        'stars' => $this->data2->getStarsCount() <=> $this->data1->getStarsCount(),
                        'forks' => $this->data2->getForksCount() <=> $this->data1->getForksCount(),
                        'pullsOpen' => $this->data2->getPullsOpenCount() <=> $this->data1->getPullsOpenCount(),
                        'pullsClosed' => $this->data2->getPullsClosedCount() <=> $this->data1->getPullsClosedCount(),
                    ]),
                ];
            }

            public function jsonSerialize(): array
            {
                $serialize = static function (RepoData $data): array {
                    return $data->jsonSerialize();
                };

                return [
                    'stats' => array_map($serialize, $this->getStats()),
                    'diffs' => array_map($serialize, $this->getDiffs()),
                ];
            }
        };
    }

    public function getFullName(string $url): string
    {
        if (!preg_match(self::URL_REGEXP, $url, $matches)) {
            throw new InvalidArgumentException('Invalid repository url');
        }

        return $matches['full_name'];
    }
}
