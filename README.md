# pimcore-vue-translations

Simple package to integrate Vue translations into Pimcore.

## Dependencies

| Release | Supported Pimcore Versions | Supported Symfony Versions | Branch |
| ------- | -------------------------- | -------------------------- | ------ |
| **1.x** | `11.0`                     | `6.2`                      | main   |

## Installation

You can install the package via composer:

```bash
composer require ntriga/pimcore-vue-translations
```

Add Bundle to `bundles.php`:

```php
return [
    Ntriga\PimcoreVueTranslations\PimcoreVueTranslationsBundle::class => ['all' => true],
];
```

## Usage

### Getting the translations
Inject translations into your Twig template to preload them for your Vue app.
The package provides the `pimcore_translations` twig function that fetches the translation messages for a given language.
For example, in your layout template add:

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your App</title>
    {# Other head elements #}
</head>
<body>
    <div id="app"></div>

    <script>
      // Preload the translations and current locale.
      window.__TRANSLATIONS__ = {{ pimcore_translations(app.locale)|json_encode()|raw }};
      window.__LOCALE__ = '{{ app.locale }}';
    </script>
    <script src="/build/app.js"></script>
</body>
</html>
```

### Vue
Configure your Vue i18n (or other vue translation package) instance with the preloaded translations. For example:

```javascript
import axios from 'axios';
import { createI18n } from 'vue-i18n';

// Use the translations and locale injected by Twig.
const messages = window.__TRANSLATIONS__ || { en: {} };
const locale = window.__LOCALE__ || 'en';

const i18n = createI18n({
    locale,
    fallbackLocale: 'en',
    messages,
});

export default i18n;
```

### Registering missing translation keys
The package provides an endpoint where you can send a single, or multiple translatation keys to to register them in the Pimcore shared translations.

To make the route available in your application, add the following in `config/routes/vue-translations.yaml`:

```yaml
pimcore_vue_translations:
    resource: '@PimcoreVueTranslationsBundle/Controller/'
    type: attribute
    prefix: /translations-api
```

After that is done, integrate a missing key handler.
Example with i18n:

```javascript
import axios from "axios";
import { createI18n } from "vue-i18n";
import { debounce } from "lodash";

const messages = window.__TRANSLATIONS__ || { en: {} };
const locale = window.__LOCALE__ || "en";

const missingKeys = new Set();

const sendMissingKeys = debounce(() => {
    if (missingKeys.size > 0) {
        const keys = Array.from(missingKeys);
        axios.post("/translations-api/register-missing-translations", { 
            keys,
            locale,
         })
            .catch((error) =>
                console.error("Error registering missing keys:", error)
            );
        missingKeys.clear();
    }
}, 1000);

const i18n = createI18n({
    locale: locale,
    fallbackLocale: "en",

    messages,

    missing: (locale, key) => {
        console.warn(
            `Missing translation key: "${key}" for locale "${locale}"`
        );

        missingKeys.add(key);
        sendMissingKeys();

        return key;
    },
});

export default i18n;
```

## Changelog
Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits
- [All contributors](../../contributors)

## License
GNU General Public License version 3 (GPLv3). Please see [License File](./LICENSE.md) for more information.