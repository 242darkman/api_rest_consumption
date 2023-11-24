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

    #[Route('/api-protege-patch.php', name: 'api_protege_patch', methods: ['PATCH'])]
    public function partialChange(Request $request, EnterpriseStorageService $enterpriseStorageService): Response
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token || !$this->authorizationChecker->isGranted('ROLE_API')) {
            return new JsonResponse(['message' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        if (null === $data) {
            return new JsonResponse(['message' => 'Format JSON invalide'], Response::HTTP_BAD_REQUEST);
        }

        $idEnterprise = $data['siren'];
        $newData = array_intersect_key($data, array_flip(['siren', 'Raison_sociale']));

        $enterprises = $enterpriseStorageService->loadEnterprises();
        if (!isset($enterprises[$idEnterprise])) {
            return new JsonResponse(['message' => 'Aucune entreprise avec ce SIREN'], Response::HTTP_NOT_FOUND);
        }

        try {
            $enterprise = $enterpriseStorageService->updateEnterpriseData($idEnterprise, $newData);
            $responseContent = [
                'enterprise' => $enterprise,
                '_links' => [
                    'self' => [
                        'href' => '/enterprise/'.$idEnterprise,
                    ]
                ],
            ]; 

            return new JsonResponse($responseContent, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api-protege-delete.php', name: 'api_protege_delete', methods: ['DELETE'])]
    public function delete(Request $request, EnterpriseStorageService $enterpriseStorageService): Response
    {
        $token = $this->tokenStorage->getToken();
        $data = json_decode($request->getContent(), true);


        if (null === $token || !$this->authorizationChecker->isGranted('ROLE_API')) {
            return new JsonResponse(['message' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }


        if (null === $data) {
            return new JsonResponse(['message' => 'Format JSON invalide'], Response::HTTP_BAD_REQUEST);
        }

        $idEnterprise = $data['siren'];
        $enterprises = $enterpriseStorageService->loadEnterprises();
        if (!isset($enterprises[$idEnterprise])) {
            return new JsonResponse(['message' => 'Aucune entreprise avec ce SIREN'], Response::HTTP_NOT_FOUND);
        }

        try {
            $enterpriseStorageService->deleteEnterprise($idEnterprise);
            return new JsonResponse(['message' => 'Entreprise supprimée avec succès'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
