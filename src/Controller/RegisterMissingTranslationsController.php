<?php

namespace Ntriga\PimcoreVueTranslations\Controller;

use Ntriga\PimcoreVueTranslations\Service\TranslationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class RegisterMissingTranslationsController extends AbstractController
{
    public function __construct(private TranslationService $translationService) {}

    #[Route('/register-missing-translations', name: 'register_missing_translations', methods: ['POST'])]
    public function registerTranslation(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $keys = [];
        if (isset($data['keys']) && is_array($data['keys'])) {
            $keys = $data['keys'];
        } elseif (isset($data['key'])) {
            $keys[] = $data['key'];
        }

        $locale = $data['locale'] ?? null;

        if (empty($keys)) {
            return new JsonResponse(['message' => 'Key is required.'], 400);
        }

        if (empty($locale)) {
            return new JsonResponse(['message' => 'Locale is required.'], 400);
        }

        $responses = [];
        foreach ($keys as $key) {
            $response = $this->translationService->registerMissingKey($key, $locale);
            $responses[$key] = json_decode($response->getContent(), true);
        }

        return new JsonResponse($responses, 200);
    }
}
