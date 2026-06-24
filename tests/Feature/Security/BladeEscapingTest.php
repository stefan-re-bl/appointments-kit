<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class BladeEscapingTest extends TestCase
{
    #[Test]
    public function blade_views_do_not_use_raw_html_output(): void
    {
        $viewPath = resource_path('views');

        $this->assertDirectoryExists($viewPath);

        $files = File::allFiles($viewPath);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php' || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = File::get($file->getPathname());
            $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());

            $this->assertStringNotContainsString(
                '{!!',
                $contents,
                "Raw Blade echo found in {$relativePath}. Use escaped {{ }} output instead."
            );

            $this->assertStringNotContainsString(
                '<?=',
                $contents,
                "Raw PHP short echo found in {$relativePath}. Use escaped {{ }} output instead."
            );

            $this->assertStringNotContainsString(
                'x-html',
                $contents,
                "Alpine x-html found in {$relativePath}. Avoid injecting raw HTML into the DOM."
            );

            $this->assertDoesNotMatchRegularExpression(
                '/@php[\s\S]*?\becho\b[\s\S]*?@endphp/u',
                $contents,
                "Raw echo inside @php block found in {$relativePath}. Use escaped {{ }} output instead."
            );
        }
    }
}