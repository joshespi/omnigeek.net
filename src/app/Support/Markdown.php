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
