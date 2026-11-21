<?php

declare(strict_types=1);

namespace Ttt;

final class Breadcrumb
{
    /**
     * Render "Home > a > b" navigation HTML.
     *
     * @param list<string> $crumbs Folder names from the root down.
     * @param string $curPath The current folder as '/a/b' (shown as plain text, not a link); '' for none.
     */
    public static function render(array $crumbs, string $curPath, Urls $urls): string
    {
        $output = '<a href="'.htmlspecialchars($urls->home()).'">Home</a>';
        $path = '';
        foreach ($crumbs as $name) {
            if ($name === '') {
                continue;
            }
            $path .= '/'.$name;
            $label = htmlspecialchars($name);
            $output .= $path === $curPath
                ? ' &gt; '.$label
                : ' &gt; <a href="'.htmlspecialchars($urls->folder($path)).'">'.$label.'</a>';
        }
        return $output;
    }
}
