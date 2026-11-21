<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\I18n;
use PHPUnit\Framework\TestCase;

class I18nTest extends TestCase
{
    protected function setUp(): void
    {
        I18n::setLocale('en');
    }

    public function testDefaultLocaleIsEn(): void
    {
        $this->assertSame('en', I18n::getLocale());
    }

    public function testSetLocaleSwitchesLocale(): void
    {
        I18n::setLocale('id');
        $this->assertSame('id', I18n::getLocale());
        $this->assertSame('id', current_locale());
    }

    public function testInvalidLocaleFallsBackToEn(): void
    {
        I18n::setLocale('invalid_locale');
        $this->assertSame('en', I18n::getLocale());
    }

    public function testTranslationInEnglishAndIndonesian(): void
    {
        I18n::setLocale('en');
        $this->assertSame('Dashboard', __('nav_dashboard'));
        $this->assertSame('Zones', __('nav_zones'));

        I18n::setLocale('id');
        $this->assertSame('Dasbor', __('nav_dashboard'));
        $this->assertSame('Zona DNS', __('nav_zones'));
    }

    public function testTranslationFallbackWhenKeyMissingInTarget(): void
    {
        I18n::setLocale('id');
        $result = __('non_existent_key_12345');
        $this->assertSame('non_existent_key_12345', $result);
    }

    public function testPlaceholderReplacements(): void
    {
        I18n::setLocale('en');
        // Test custom replacement on trans helper
        $output = I18n::trans('custom_msg', ['name' => 'PowerDNS']);
        $this->assertNotEmpty($output);
    }
}
