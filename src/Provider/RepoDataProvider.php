<?php declare(strict_types=1);

namespace App\Provider;

use App\Data\RepoData;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Webmozart\Assert\Assert;

class RepoDataProvider implements RepoDataProviderInterface
{
    protected HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getData(string $owner, string $name): RepoData
    {
        Assert::stringNotEmpty($owner);
        Assert::stringNotEmpty($name);

        $client = $this->client->withOptions([
            'base_uri' => "https://api.github.com/repos/${owner}/${name}/",
        ]);

        $data = $client->request('GET', "/")->toArray();

        Assert::keyExists($data, 'full_name');
        Assert::keyExists($data, 'subscribers_count');
        Assert::keyExists($data, 'stargazers_count');
        Assert::keyExists($data, 'forks_count');

        [
            'full_name' => $fullName,
            'subscribers_count' => $watchers,
            'stargazers_count' => $stars,
            'forks_count' => $forks,
        ] = $data;

        Assert::same($fullName, "{$owner}/{$name}");

        $data = $client->request('GET', "/pulls")->toArray();

        Assert::allKeyExists($data, 'state');

        $data = array_column($data, 'state');

        Assert::allInArray($data, ['open', 'closed']);

        [
            'open' => $open,
            'closed' => $closed,
        ] = array_count_values($data) + ['open' => 0, 'closed' => 0];

        return new RepoData($fullName, $watchers, $watchers, $stars, $forks, $open, $closed);
    }
}
