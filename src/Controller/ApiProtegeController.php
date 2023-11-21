<?php

namespace App\Controller;

use App\Service\EnterpriseStorageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ApiProtegeController extends AbstractController
{
    private $tokenStorage;
    private $authorizationChecker;

    public function __construct(TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Retrieves a list of enterprises from the storage service.
     *
     * @param EnterpriseStorageService $enterpriseStorageService The service used to load enterprises.
     * @return JsonResponse The JSON response containing the list of enterprises.
     * @throws \Exception If an error occurs while retrieving the enterprises.
     */
    #[Route('/api-protege.php', name: 'api_protege_get', methods: ['GET'])]
    public function getEnterprises(EnterpriseStorageService $enterpriseStorageService): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token || !$this->authorizationChecker->isGranted('ROLE_API')) {
            return $this->json(['error' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $entreprises = $enterpriseStorageService->loadEnterprises();

            if (empty($entreprises)) {
                return new JsonResponse(['error' => 'Aucune entreprise trouvée'], Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse(['entreprises' => $entreprises], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
