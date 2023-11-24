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

        if (strlen($searchTerm) < 3 && $searchTerm !== "") {
            $this->addFlash('error', 'La chaîne de recherche doit comporter au moins 3 caractères.');
        } else {
            if ($searchTerm !== "") {
                $requestToApi = $entrepriseApiConsumption->search($searchTerm, $page, $perPage);
                $enterprises = $enterpriseDTOTransformer->transformAll($requestToApi['results']);
                $total_pages = intval($requestToApi['total_pages'], 10);

                if(empty($enterprises)){
                    $this->addFlash('warning', 'Aucun résultat trouvé pour la recherche ' . $searchTerm);
                }
            }
        }

        $showPrevious = $page > 1;
        $showNext = $total_pages > 2 && $page !== $total_pages;

        return $this->render('home/index.html.twig', [
            'search_term' => $searchTerm,
            'enterprises' => $enterprises,
            'current_page' => $page,
            'total_pages' => $total_pages,
            'show_previous' => $showPrevious,
            'show_next' => $showNext,
        ]);
    }


    #[Route('/enterprise/{siren}', name: 'save_enterprise')]
    public function saveEnterprise(
        string $siren,
        EnterpriseApiConsumption $entrepriseApiConsumption,
        EnterpriseStorageService $enterpriseStorageService,
        EnterpriseDTOTransformer $enterpriseDTOTransformer
    ): Response
    {
        $fetchEnterprises = $entrepriseApiConsumption->search($siren);
        $enterpriseData = $enterpriseDTOTransformer->transform($fetchEnterprises['results']);

        if (!$enterpriseData) {
            throw $this->createNotFoundException('The enterprise does not exist');
        }

        $enterpriseStorageService->storeInFileUnique($enterpriseData);

        return $this->render('home/detail.html.twig', [
            'enterprise' => $enterpriseData,
        ]);
    }

}
