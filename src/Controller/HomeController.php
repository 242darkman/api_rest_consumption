<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\EnterpriseApiConsumption;
use App\Service\EnterpriseDTOTransformer;
use App\Service\EnterpriseStorageService;

class HomeController extends AbstractController
{

    #[Route('/', name: 'app_home')]
    public function index(
        Request $request, 
        EnterpriseApiConsumption $entrepriseApiConsumption,
        EnterpriseDTOTransformer $enterpriseDTOTransformer
    ): Response
    {
        $searchTerm = $request->query->get('search', '');
        $page = $request->query->getInt('page', 1);
        $enterprises = [];
        $perPage = 10;
        $total_pages = 0;
        $searchPerformed = false;

        if ($searchTerm !== "") {
            $requestToApi = $entrepriseApiConsumption->search($searchTerm, $page, $perPage);
            $enterprises = $enterpriseDTOTransformer->transformAll($requestToApi['results']);
            $total_pages = intval($requestToApi['total_pages'], 10);
            $searchPerformed = true;
        }

        $showPrevious = $page > 1;
        $showNext = $total_pages > 2 && $page !== $total_pages;

        return $this->render('home/index.html.twig', [
            'search_term' => $searchTerm,
            'enterprises' => $enterprises,
            'search_performed' => $searchPerformed,
            'current_page' => $page,
            'show_previous' => $showPrevious,
            'show_next' => $showNext,
        ]);
    }
}
