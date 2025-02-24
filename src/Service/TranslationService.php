<?php

namespace Ntriga\PimcoreVueTranslations\Service;

use Pimcore\Model\Translation;
use Pimcore\Tool;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationService
{
    public function __construct(private CacheItemPoolInterface $cache, private TranslatorInterface $translator, private int $cacheTTL = 3600) {}

    /**
     * Retrieves translations for the specified locale.
     * Uses caching to optimize performance.
     */
    public function getTranslationsForLocale(string $locale): array
    {
        $cacheKey = 'pimcore_translations_' . $locale;
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $translations = $this->fetchTranslationsFromPimcore($locale);

        // Return translations only for the requested locale.
        return $translations;
    }

    public function registerMissingKey(string $key, string $locale): JsonResponse
    {
        $translation = Translation::getByKey($key);

        if ($translation instanceof Translation) {
            return new JsonResponse(['message' => 'Key already exists, skipped.'], 200);
        }

        $websiteLocales = Tool::getValidLanguages();

        $translations = [$locale => ''];

        $translation = new Translation();
        $translation->setKey($key);
        $translation->setTranslations($translations);
        $translation->save();

        return new JsonResponse(['message' => 'Key registered'], 200);
    }

    private function fetchTranslationsFromPimcore(string $locale): array
    {
        $translationsListing = new Translation\Listing();

        $translations = [];

        foreach ($translationsListing as $translation) {
            $key = $translation->getKey();
            foreach ($translation->getTranslations() as $translationLocale => $text) {
                if ($translationLocale !== $locale || empty($text)) {
                    continue;
                }

                $translations[$translationLocale][$key] = $text;
            }
        }

        return $translations;
    }
}
