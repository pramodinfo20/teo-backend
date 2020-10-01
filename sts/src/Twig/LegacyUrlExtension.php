<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LegacyUrlExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('legacyUrl', [$this, 'setLegacyUrl']),
            new TwigFunction('returnLegacyUrl', [$this, 'setReturnLegacyUrl']),
        ];
    }

    /**
     * Fix legacy URL
     *
     * @param string $url
     * @param array  $parameters
     * @param bool   $overrideParameters
     *
     * @return string
     */
    public function setLegacyUrl($url, array $parameters = [], $overrideParameters = false): string
    {
        if (count($parameters) > 0) {
            if ($overrideParameters) {
                return $this->replaceQuery($url, $parameters);
            }

            return $url . '&' . http_build_query($parameters);
        }

        return $url;
    }

    /**
     * @param string $url
     * @param null   $query
     * @param bool   $recursive
     *
     * @return string
     */
    private function replaceQuery($url, $query = null, $recursive = false): string
    {
        $urlComponents = parse_url($url);

        if (empty($urlComponents['query']))
            return $url . '?' . http_build_query($query);

        parse_str($urlComponents['query'], $originalQueryString);

        if ($recursive == true)
            $mergedResult = array_merge_recursive($originalQueryString, $query);
        else
            $mergedResult = array_merge($originalQueryString, $query);

        return str_replace($urlComponents['query'], http_build_query($mergedResult), $url);
    }

    /**
     * Fix return URL
     *
     * @param string $url
     * @param array  $parameters
     *
     * @return string
     */
    public function setReturnLegacyUrl(string $url, array $parameters = []): string
    {
        if (count($parameters) > 0)
            return $this->getUrlWithoutQueryParameters($url, $parameters);

        return $url;
    }

    /**
     * @param string $url
     * @param array  $keys
     *
     * @return string
     */
    private function getUrlWithoutQueryParameters(string $url, array $keys): string
    {
        foreach ($keys as $key) {
            $url = preg_replace('/(?:&|(\?))' . $key . '=[^&]*(?(1)&|)?/i', "$1", $url);
            $url = rtrim($url, '?');
            $url = rtrim($url, '&');
        }

        return $url;
    }
}