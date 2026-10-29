<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

final class DocumentHtml
{
    public static function sanitize(?string $html): string
    {
        static $purifier;

        if (! $purifier) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('Cache.DefinitionImpl', null);
            $config->set('HTML.Allowed', 'p[class|style],div[class|style],br,strong,b,em,i,u,s,strike,sup,sub,span[class|style],ol,ul,li[class|data-list]');
            $config->set('CSS.AllowedProperties', ['color', 'background-color', 'text-align']);
            $config->set('Attr.AllowedClasses', [
                'ql-align-center', 'ql-align-right', 'ql-align-justify',
                'ql-direction-rtl', 'ql-ui',
                'ql-indent-1', 'ql-indent-2', 'ql-indent-3', 'ql-indent-4',
                'ql-indent-5', 'ql-indent-6', 'ql-indent-7', 'ql-indent-8',
            ]);
            $definition = $config->getHTMLDefinition(true);
            $definition->addElement('s', 'Inline', 'Inline', 'Common');
            $definition->addAttribute('li', 'data-list', 'Enum#ordered,bullet,checked,unchecked');
            $purifier = new HTMLPurifier($config);
        }

        return $purifier->purify($html ?? '');
    }
}
