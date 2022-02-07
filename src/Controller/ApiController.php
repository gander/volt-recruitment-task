<?php

namespace App\Controller;

use App\Processor\RepoDataProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/api', name: 'api')]
    public function index(RepoDataProcessor $processor, Request $request): Response
    {
        $repo1 = (string)$request->query->get('repo1');
        $repo2 = (string)$request->query->get('repo2');

        $result = $processor->compareRepos($repo1, $repo2);

        return $this->json(
            $result,
            200,
            [], [
            'json_encode_options' => JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_PRETTY_PRINT,
        ]);
    }
}
