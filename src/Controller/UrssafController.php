<?php

namespace App\Controller;

use App\Form\SalaireInputType;
use App\Service\UrssafApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UrssafController extends AbstractController
{
    #[Route('/urssaf_evaluation', name: 'app_urssaf_evaluate')]
    public function index(Request $request, UrssafApiService $urssafApiService): Response
    {
        $form = $this->createForm(SalaireInputType::class);
        $form->handleRequest($request);
        $employee_contribution = 0;
        $employer_cost = 0;
        $end_of_contract_indemnity = 0;
        $gross_salary = 0;

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $selectedContractType = $data['contractType'];
            $grossSalary = $data['grossSalary'];
            $selectedContractType = $data['contractType'];
            
            $calculationResult = $urssafApiService->evaluate($grossSalary, $selectedContractType);
           // dd($calculationResult);
            $employee_contribution = $calculationResult['employee_contribution']['nodeValue'];
            $employer_cost = $calculationResult['employer_cost']['nodeValue'];
            $gross_salary = $calculationResult['net_salary']['nodeValue'];
            $end_of_contract_indemnity = $selectedContractType === 'fixed_term' ? $calculationResult['end_of_contract_indemnity']['nodeValue'] : 0;
            //$end_of_contract_indemnity = 10;
        }

        return $this->render('urssaf/index.html.twig', [
            'controller_name' => 'UrssafController',
            'form' => $form->createView(),
            'employee_contribution' => $employee_contribution,
            'employer_cost' => $employer_cost,
            'end_of_contract_indemnity' => $end_of_contract_indemnity,
            'gross_salary' => $gross_salary,
        ]);
    }

    #[Route('/api/urssaf/calculate', name: 'api_urssaf_calculate', methods: ['POST'])]
    public function calculate(Request $request, UrssafApiService $urssafApiService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $grossSalary = $data['grossSalary'];
        $contractType = $data['contractType'];

        $result = $urssafApiService->evaluate($grossSalary, $contractType);

        return $this->json($result);
    }

}
