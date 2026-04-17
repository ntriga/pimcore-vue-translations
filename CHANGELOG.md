# Changelog

All notable changes to `pimcore-vue-translations` will be documented in this file.

## 1.1.0 - 2026-04-17

- Resolve Pimcore shared-translation fallback chains into the requested locale
  when returning Vue translation payloads.
- Cache resolved translation payloads correctly.
- Register missing translation keys for all configured Pimcore valid languages.
