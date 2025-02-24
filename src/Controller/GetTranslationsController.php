<?php

namespace Ntriga\PimcoreVueTranslations\Controller;

use Ntriga\PimcoreVueTranslations\Service\TranslationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class GetTranslationsController extends AbstractController
{
    public function __construct(private TranslationService $translationService) 
    {}

    public function getTranslations(Request $request): JsonResponse
    {
        $locale = $request->query->get('locale', 'en');

        $translations = $this->translationService->getTranslationsForLocale($locale);

        return new JsonResponse($translations);
    }
}