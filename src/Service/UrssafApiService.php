<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class UrssafApiService
{
  private $client;
    private $apiUrl = 'https://mon-entreprise.urssaf.fr/api/v1/evaluate';
    private $expressions = [];

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->expressions = [
            'permanent' => 'salarié . rémunération . net . à payer avant impôt',
            'internship' => 'salarié . contrat . stage . gratification minimale',
            'apprenticeship' => 'salarié . contrat . apprentissage',
            'fixed_term' => 'salarié . rémunération . net . à payer avant impôt',
            'employee_contribution' => 'salarié . cotisations . salarié',
            'employer_cost' => 'salarié . coût total employeur',
            'end_of_contract_indemnity' => 'salarié . contrat . CDD . indemnité de fin de contrat',
        ];
    }

    /**
     * Calcul du salaire d'un employé, du coût de l'employeur
     * 
     * @param float $grossSalary : salaire brut
     * @param string $status : le statut de l'employé (CDI, CDD, apprentissage, stage)
     * @param string $expression : l'expression permet de pouvoir effectuer le calcul du salaire net
     */
    public function calculateNetSalaryByStatus(float $grossSalary, string $status, string $expression)
    {
        try {
            $response = $this->client->request('POST', $this->apiUrl, [
                'json' => [
                    'situation' => [
                        'salarié . contrat . salaire brut' => [
                            'valeur' => $grossSalary,
                            'unité' => '€ / mois',
                        ],
                        'salarié . contrat' => $status,
                    ],
                    'expressions' => [
                        $expression,
                    ],
                ],
            ]);

            if (200 !== $response->getStatusCode()) {
                throw new \Exception('Failed to retrieve data from the API.');
            }

            $content = $response->getContent();
            $data = json_decode($content, true);

            if (null === $data || !isset($data['evaluate'][0])) {
                throw new \Exception('Invalid or missing data in the API response.');
            }

            $result = [
                'nodeValue' => $data['evaluate'][0]['nodeValue'],
            ];

            return $result;

        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }


    public function getContractType(string $status){
      return [
        'permanent' => 'CDI',
        'fixed_term' => 'CDD',
        'apprenticeship' => 'apprentissage',
        'internship' => 'stage',
      ][$status];
    }

    public function evaluate(float $grossSalary, string $status){
      try {
        $contractType = $this->getContractType($status);
        $netSalaryPermanent = 0;
        $netSalaryFixedTerm = 0;
        $netSalaryApprenticeship = 0;
        $minimumRemunerationInternship = 0;
        $employerCost = 0;
        $employeeContribution = 0;
        $endOfContractIndemnity = 0;

        switch ($contractType) {
          case 'CDI':
            $employeeContribution = $this->calculateNetSalaryByStatus($grossSalary, $contractType, $this->expressions['employee_contribution']);
            $employerCost = $this->calculateNetSalaryByStatus($grossSalary, $contractType, $this->expressions['employer_cost']);
            $netSalaryPermanent = $this->calculateNetSalaryByStatus($grossSalary, $contractType, $this->expressions['permanent']);

            return [
              'contract_type' => $contractType,
              'employee_contribution' => $employeeContribution,
              'employer_cost' => $employerCost,
              'net_salary' => $netSalaryPermanent,
            ];
            break;

          case 'CDD':
            $employeeContribution = $this->calculateNetSalaryByStatus($grossSalary, $contractType, $this->expressions['employee_contribution']);
            $employerCost = $this->calculateNetSalaryByStatus($grossSalary, $contractType, $this->expressions['employer_cost']);
            $endOfContractIndemnity = $this->calculateNetSalaryByStatus($grossSalary, $contractType, $this->expressions['end_of_contract_indemnity']);
            $netSalaryFixedTerm = $this->calculateNetSalaryByStatus($grossSalary, $contractType, $this->expressions['fixed_term']);

            return [
              'contract_type' => $contractType,
              'employee_contribution' => $employeeContribution,
              'employer_cost' => $employerCost,
              'end_of_contract_indemnity' => $endOfContractIndemnity,
              'net_salary' => $netSalaryFixedTerm,
            ];

          case 'apprentissage':
            $employeeContribution = $this->calculateNetSalaryByStatus($grossSalary, $contractType, $this->expressions['employee_contribution']);
            $employerCost = $this->calculateNetSalaryByStatus($grossSalary, $contractType, $this->expressions['employer_cost']);
            $netSalaryApprenticeship = $this->calculateNetSalaryByStatus($grossSalary, $contractType, $this->expressions['apprenticeship']);

            return [
              'contract_type' => $contractType,
              'employee_contribution' => $employeeContribution,
              'employer_cost' => $employerCost,
              'net_salary' => $netSalaryApprenticeship,
            ];

          case 'stage':
            $employeeContribution = $this->calculateNetSalaryByStatus($grossSalary, $contractType, $this->expressions['employee_contribution']);
            $employerCost = $this->calculateNetSalaryByStatus($grossSalary, $contractType, $this->expressions['employer_cost']);
            $minimumRemunerationInternship = $this->calculateNetSalaryByStatus($grossSalary, $contractType, $this->expressions['internship']);

            return [
              'contract_type' => $contractType,
              'employee_contribution' => $employeeContribution,
              'employer_cost' => $employerCost,
              'net_salary' => $minimumRemunerationInternship,
            ];
        }
      } catch (\Exception $e) {
        error_log('Error in UrssafApiService: ' . $e->getMessage());
        return [
            'error' => $e->getMessage()
        ];
      }
    }
}