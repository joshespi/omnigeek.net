<?php

namespace App\Support;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;

class Markdown
{
    private static ?MarkdownConverter $converter = null;

    public static function render(string $text): string
    {
        return (string) self::converter()->convert($text);
    }

    // Rendered Markdown reduced to one line of plain text (for excerpts and meta descriptions).
    public static function plain(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(self::render($text)), ENT_QUOTES | ENT_HTML5)));
    }

    private static function converter(): MarkdownConverter
    {
        if (self::$converter === null) {
            $env = new Environment(['html_input' => 'strip', 'allow_unsafe_links' => false]);
            $env->addExtension(new CommonMarkCoreExtension);
            self::$converter = new MarkdownConverter($env);
        }

        return self::$converter;
    }
}
