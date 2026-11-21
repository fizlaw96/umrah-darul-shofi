<?php

use Illuminate\Support\Facades\File;

/**
 * Public pages load a Material Symbols subset; an icon missing from it renders as its ligature text.
 *
 * @return array<int, string>
 */
function subsetIconNames(string $view): array
{
    preg_match('/Material\+Symbols\+Outlined[^"]*icon_names=([a-z0-9_,]+)/', File::get(resource_path("views/{$view}")), $matches);

    return explode(',', $matches[1] ?? '');
}

/**
 * @return array<int, string>
 */
function literalIconsUsedOutsideAdmin(): array
{
    $icons = [];

    foreach (File::allFiles(resource_path('views')) as $file) {
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname());

        if (str_starts_with($path, 'admin/') || str_starts_with($path, 'components/admin/') || $path === 'layouts/admin.blade.php') {
            continue;
        }

        preg_match_all('/material-symbols-outlined[^>]*>(.*?)<\//s', $file->getContents(), $matches);

        foreach ($matches[1] as $content) {
            $content = trim($content);

            if (preg_match('/^[a-z0-9_]+$/', $content) === 1) {
                $icons[] = $content;
            } else {
                preg_match_all("/'([a-z0-9_]+)'(?!\\])/", $content, $literals);
                array_push($icons, ...$literals[1]);
            }
        }
    }

    foreach (File::allFiles(app_path()) as $file) {
        preg_match_all("/'icon'\\s*=>\\s*'([a-z0-9_]+)'/", $file->getContents(), $matches);
        array_push($icons, ...$matches[1]);
    }

    return array_values(array_unique($icons));
}

it('includes every public icon in the subset icon font', function (string $view) {
    expect(array_diff(literalIconsUsedOutsideAdmin(), subsetIconNames($view)))->toBe([]);
})->with(['layouts/app.blade.php', 'auth/admin-login.blade.php']);
