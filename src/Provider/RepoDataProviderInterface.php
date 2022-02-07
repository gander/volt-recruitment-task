<?php declare(strict_types=1);

namespace App\Provider;

use App\Data\RepoData;

interface RepoDataProviderInterface
{
    public function getData(string $owner, string $name): RepoData;
}
