<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\View;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    private View $view;

    protected function setUp(): void
    {
        $this->view = new View(dirname(__DIR__, 3));
    }

    public function testAuthLoginViewRendersWithoutAdminLayout(): void
    {
        $html = view('auth.login', ['csrfToken' => 'test-csrf-token']);

        // Must contain login elements
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('data-theme', $html);

        // Must NOT contain admin dashboard layout elements
        $this->assertStringNotContainsString('class="admin-layout"', $html);
        $this->assertStringNotContainsString('class="admin-sidebar"', $html);
        $this->assertStringNotContainsString('class="admin-header"', $html);
    }

    public function testViewDisableLayout(): void
    {
        $html = $this->view->render('auth.login', ['csrfToken' => 'token'], null);
        $this->assertStringNotContainsString('class="admin-sidebar"', $html);
    }

    public function testAssetHelperGeneratesValidPath(): void
    {
        $path = View::asset('assets/css/app.css');
        $this->assertStringStartsWith('/assets/css/app.css', $path);
    }
}
