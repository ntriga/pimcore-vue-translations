<?php

namespace Ntriga\PimcoreVueTranslations\Twig\Extension;

use Ntriga\PimcoreVueTranslations\Service\TranslationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PimcoreVueTranslationsExtension extends AbstractExtension
{
    public function __construct(private TranslationService $translationService) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('pimcore_translations', [$this, 'getTranslations']),
        ];
    }

    public function getTranslations(string $locale): array
    {
        return $this->translationService->getTranslationsForLocale($locale);
    }
}
