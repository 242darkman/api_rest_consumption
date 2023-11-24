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

        return $this->render('urssaf/index.html.twig', [
            'form' => $form->createView(),
            'employee_contribution' => number_format($employee_contribution, 2),
            'employer_cost' => number_format($employer_cost, 2),
            'end_of_contract_indemnity' => number_format($end_of_contract_indemnity, 2),
            'gross_salary' => number_format($gross_salary, 2),
        ]);
    }

    #[Route('/api/urssaf/calculate', name: 'api_urssaf_calculate', methods: ['POST'])]
    public function calculate(Request $request, UrssafApiService $urssafApiService): Response
    {
        try {
            $formRequest = $request->request->all();

            if (!isset($formRequest['salaire_input'])) {
                return $this->json(['error' => 'Missing salaire_input data'], Response::HTTP_BAD_REQUEST);
            }

            $salaireInput = $formRequest['salaire_input'];
            $grossSalary = $salaireInput['grossSalary'] ?? null;
            $totalNetSalariesEarned = $salaireInput['totalCddSalary'] ?? null;
            $selectedContractType = $salaireInput['contractType'] ?? null;

            if (null === $grossSalary || null === $selectedContractType) {
                return $this->json(['error' => 'Missing data'], Response::HTTP_BAD_REQUEST);
            }

            $result = $urssafApiService->evaluate($grossSalary, $selectedContractType);
            $end_of_contract_indemnity = $selectedContractType === 'fixed_term' ? ($totalNetSalariesEarned * (10/100)) : 0;

            return $this->json([
                'employee_contribution' => number_format($result['employee_contribution']['nodeValue'], 2),
                'employer_cost' => number_format($result['employer_cost']['nodeValue'], 2),
                'end_of_contract_indemnity' => number_format($end_of_contract_indemnity, 2),
                'gross_salary' => number_format($result['net_salary']['nodeValue'], 2),
            ]);
        } catch (\Exception $e) {
            error_log('Error in API: ' . $e->getMessage());
            return $this->json(['error' => 'Server error : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
