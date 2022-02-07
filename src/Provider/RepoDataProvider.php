<?php declare(strict_types=1);

namespace App\Provider;

use App\Data\RepoData;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Webmozart\Assert\Assert;

class RepoDataProvider implements RepoDataProviderInterface
{
    protected HttpClientInterface $client;

    public function __construct(HttpClientInterface $githubClient)
    {
        $this->client = $githubClient;
    }

    public function getData(string $fullName): RepoData
    {
        Assert::regex($fullName, '~^[a-zA-Z0-9-_.]+/[a-zA-Z0-9-_.]+$~');

        $data = $this->client->request('GET', "https://api.github.com/repos/${fullName}")->toArray();

        Assert::keyExists($data, 'full_name');
        Assert::keyExists($data, 'subscribers_count');
        Assert::keyExists($data, 'stargazers_count');
        Assert::keyExists($data, 'forks_count');

        [
            'full_name' => $fullName2,
            'subscribers_count' => $watchers,
            'stargazers_count' => $stars,
            'forks_count' => $forks,
        ] = $data;

        Assert::same($fullName2, $fullName);

        $data = $this->client->request('GET', "https://api.github.com/repos/${fullName}/pulls")->toArray();

        Assert::allKeyExists($data, 'state');

        $data = array_column($data, 'state');

        Assert::allInArray($data, ['open', 'closed']);

        [
            'open' => $pullsOpen,
            'closed' => $pullsClosed,
        ] = array_count_values($data) + ['open' => 0, 'closed' => 0];

        return new RepoData($fullName, compact(
            'watchers',
            'stars',
            'forks',
            'pullsOpen',
            'pullsClosed'
        ));
    }
}
