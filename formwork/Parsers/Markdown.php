<?php

namespace Formwork\Parsers;

use Formwork\Parsers\Extensions\ParsedownExtra;

class Markdown extends AbstractParser
{
    /**
     * Parse a Markdown string
     *
     * @return string
     */
    public static function parse(string $input, array $options = [])
    {
        return @ParsedownExtra::instance()->text($input, $options);
    }
}
