<?php

namespace App\Controller;

use App\Service\MarketplaceCatalog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(MarketplaceCatalog $catalog): Response
    {
        return $this->render('home/index.html.twig', [
            'courses' => array_slice($catalog->all(), 0, 3),
            'subjects' => $catalog->subjects(),
        ]);
    }
}
