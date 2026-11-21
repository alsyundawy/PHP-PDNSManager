<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Enterprise Internationalization (i18n) Engine
 *
 * Provides thread-safe, fast dictionary lookups with fallback support
 * for dual-language English (en) and Indonesian (id) localization.
 */
class I18n
{
    private static ?string $currentLocale = null;

    /** @var array<string, array<string, string>> */
    private static array $dictionaries = [];

    private static string $langPath = '';

    /**
     * Set base path for language resource files.
     */
    public static function setLangPath(string $path): void
    {
        self::$langPath = rtrim($path, '/');
    }

    /**
     * Get the active locale.
     */
    public static function getLocale(): string
    {
        if (self::$currentLocale !== null) {
            return self::$currentLocale;
        }

        // 1. Check query parameter
        if (!empty($_GET['lang']) && is_string($_GET['lang'])) {
            $lang = strtolower(trim($_GET['lang']));
            if (in_array($lang, ['en', 'id'], true)) {
                self::setLocale($lang);
                return $lang;
            }
        }

        // 2. Check PHP Session
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['locale']) && is_string($_SESSION['locale'])) {
            $lang = strtolower(trim($_SESSION['locale']));
            if (in_array($lang, ['en', 'id'], true)) {
                self::$currentLocale = $lang;
                return $lang;
            }
        }

        // 3. Check Cookie
        if (!empty($_COOKIE['pdns_locale']) && is_string($_COOKIE['pdns_locale'])) {
            $lang = strtolower(trim($_COOKIE['pdns_locale']));
            if (in_array($lang, ['en', 'id'], true)) {
                self::$currentLocale = $lang;
                return $lang;
            }
        }

        // 4. Default fallback to English
        self::$currentLocale = 'en';
        return 'en';
    }

    /**
     * Set active application locale.
     */
    public static function setLocale(string $locale): void
    {
        $locale = in_array($locale, ['en', 'id'], true) ? $locale : 'en';
        self::$currentLocale = $locale;

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['locale'] = $locale;
        }
    }

    /**
     * Translate a given key with optional replacements and locale override.
     *
     * @param string $key
     * @param array<string, scalar> $replace
     * @param string|null $locale
     * @return string
     */
    public static function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        $targetLocale = $locale !== null && in_array($locale, ['en', 'id'], true)
            ? $locale
            : self::getLocale();

        self::loadDictionary($targetLocale);

        $line = self::$dictionaries[$targetLocale][$key] ?? null;

        // Fallback to English if key missing in selected locale
        if ($line === null && $targetLocale !== 'en') {
            self::loadDictionary('en');
            $line = self::$dictionaries['en'][$key] ?? null;
        }

        // If still missing, return the raw key
        if ($line === null) {
            $line = $key;
        }

        if (!empty($replace)) {
            foreach ($replace as $placeholder => $value) {
                $strVal = (string) $value;
                $line = str_replace(
                    [':' . $placeholder, '{' . $placeholder . '}'],
                    $strVal,
                    $line
                );
            }
        }

        return $line;
    }

    /**
     * Load dictionary file for the specified locale.
     */
    private static function loadDictionary(string $locale): void
    {
        if (isset(self::$dictionaries[$locale])) {
            return;
        }

        $basePath = self::$langPath !== ''
            ? self::$langPath
            : dirname(__DIR__, 2) . '/resources/lang';

        $file = $basePath . '/' . $locale . '.php';

        if (file_exists($file)) {
            $data = require $file;
            self::$dictionaries[$locale] = is_array($data) ? $data : [];
        } else {
            self::$dictionaries[$locale] = [];
        }
    }
}
