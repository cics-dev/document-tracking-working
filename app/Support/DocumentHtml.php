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
            $config->set('HTML.Allowed', 'p[class|style],div[class|style],br,strong,b,em,i,u,s,strike,sup,sub,span[class|style],ol,ul,li[class|data-list],table[style],thead,tbody,tfoot,tr,th[class|style],td[class|style|data-row|width|height|colspan|rowspan|data-cell-covered|data-merge-widths|data-original-width|data-table-width|data-table-offset|data-table-row-heights]');
            $config->set('CSS.AllowedProperties', ['color', 'background-color', 'text-align']);
            $config->set('Attr.AllowedClasses', [
                'ql-align-center', 'ql-align-right', 'ql-align-justify',
                'ql-direction-rtl', 'ql-ui',
                'ql-indent-1', 'ql-indent-2', 'ql-indent-3', 'ql-indent-4',
                'ql-indent-5', 'ql-indent-6', 'ql-indent-7', 'ql-indent-8',
                'document-cell-break',
            ]);
            $definition = $config->getHTMLDefinition(true);
            $definition->addElement('s', 'Inline', 'Inline', 'Common');
            $definition->addAttribute('li', 'data-list', 'Enum#ordered,bullet,checked,unchecked');
            $definition->addAttribute('td', 'data-row', 'Text');
            $percentage = new class extends \HTMLPurifier_AttrDef
            {
                public function validate($string, $config, $context)
                {
                    $width = trim($string);

                    return preg_match('/^\d+(\.\d+)?%$/D', $width)
                        && (float) $width >= 0 && (float) $width <= 100 ? $width : false;
                }
            };
            foreach (['width', 'data-original-width', 'data-table-width', 'data-table-offset'] as $attribute) {
                $definition->addAttribute('td', $attribute, $percentage);
            }
            $definition->addAttribute('td', 'data-cell-covered', 'Enum#true');
            $definition->addAttribute('td', 'data-table-row-heights', new class extends \HTMLPurifier_AttrDef
            {
                public function validate($string, $config, $context)
                {
                    $heights = explode(',', $string);
                    if (count($heights) > 200) {
                        return false;
                    }
                    foreach ($heights as $height) {
                        if (! preg_match('/^\d+$/D', $height) || (int) $height < 24 || (int) $height > 2000) {
                            return false;
                        }
                    }

                    return $string;
                }
            });
            $definition->addAttribute('td', 'data-merge-widths', new class extends \HTMLPurifier_AttrDef
            {
                public function validate($string, $config, $context)
                {
                    $widths = explode(',', $string);
                    if (count($widths) > 200) {
                        return false;
                    }
                    foreach ($widths as $width) {
                        if (! preg_match('/^\d+(\.\d+)?%$/D', $width) || (float) $width <= 0 || (float) $width > 100) {
                            return false;
                        }
                    }

                    return $string;
                }
            });
            $definition->addAttribute('table', 'style', new class extends \HTMLPurifier_AttrDef
            {
                public function validate($string, $config, $context)
                {
                    $styles = [];
                    foreach (explode(';', $string) as $declaration) {
                        if (preg_match('/^\s*(width|margin-left)\s*:\s*(\d+(?:\.\d+)?)%\s*$/D', $declaration, $match)
                            && (float) $match[2] <= 100) {
                            $styles[] = $match[1].': '.$match[2].'%';
                        }
                    }

                    return $styles ? implode('; ', $styles).';' : false;
                }
            });
            $purifier = new HTMLPurifier($config);
        }

        return $purifier->purify($html ?? '');
    }
}
