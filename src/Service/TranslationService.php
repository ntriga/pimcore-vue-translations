<?php

namespace Ntriga\PimcoreVueTranslations\Service;

use Pimcore\Model\Translation;
use Pimcore\Tool;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class TranslationService
{
    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly int $cacheTTL = 3600,
    ) {}

    /**
     * Retrieves translations for the specified locale.
     * Pimcore fallback locales are resolved into the requested locale's catalogue
     * so consumers can keep using a single preloaded locale payload.
     */
    public function getTranslationsForLocale(string $locale): array
    {
        $localeChain = $this->resolveLocaleChain($locale);
        $cacheKey = 'pimcore_translations_' . md5(implode('|', $localeChain));
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $translations = $this->fetchTranslationsFromPimcore($locale, $localeChain);

        $cacheItem->set($translations);
        $cacheItem->expiresAfter($this->cacheTTL);
        $this->cache->save($cacheItem);

        return $translations;
    }

    public function registerMissingKey(string $key, string $locale): JsonResponse
    {
        $translation = Translation::getByKey($key);

        if ($translation instanceof Translation) {
            return new JsonResponse(['message' => 'Key already exists, skipped.'], 200);
        }

        $translations = array_fill_keys(
            array_unique([
                ...Tool::getValidLanguages(),
                $locale,
            ]),
            ''
        );

        $translation = new Translation();
        $translation->setKey($key);
        $translation->setTranslations($translations);
        $translation->save();

        return new JsonResponse(['message' => 'Key registered'], 200);
    }

    /**
     * @param string[] $localeChain
     *
     * @return array<string, array<string, string>>
     */
    private function fetchTranslationsFromPimcore(string $locale, array $localeChain): array
    {
        $translationsListing = new Translation\Listing();
        $translations = [$locale => []];

        foreach ($translationsListing as $translation) {
            $key = $translation->getKey();
            $localizedValues = $translation->getTranslations();

            foreach ($localeChain as $translationLocale) {
                $text = trim((string) ($localizedValues[$translationLocale] ?? ''));
                if ($text === '') {
                    continue;
                }

                $translations[$locale][$key] = $text;
                break;
            }
        }

        return $translations;
    }

    /**
     * @return string[]
     */
    private function resolveLocaleChain(string $locale): array
    {
        $resolvedLocales = [];
        $visitedLocales = [];

        $appendLocale = function (string $currentLocale) use (&$appendLocale, &$resolvedLocales, &$visitedLocales): void {
            if (isset($visitedLocales[$currentLocale])) {
                return;
            }

            $visitedLocales[$currentLocale] = true;
            $resolvedLocales[] = $currentLocale;

            foreach (Tool::getFallbackLanguagesFor($currentLocale) as $fallbackLocale) {
                $appendLocale($fallbackLocale);
            }
        };

        $appendLocale($locale);

        return $resolvedLocales;
    }
}
