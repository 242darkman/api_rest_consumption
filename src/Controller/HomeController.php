<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\EnterpriseApiConsumption;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, EnterpriseApiConsumption $entrepriseApiConsumption): Response
    {
        $searchTerm = $request->query->get('search', '');
        $enterpriseResults = [];

        if ($searchTerm !== "") {
            $enterpriseResults = $entrepriseApiConsumption->search($searchTerm);
        }


        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'search_term' => $searchTerm,
            'enterprises' => $enterpriseResults['results'],
        ]);
    }
}
