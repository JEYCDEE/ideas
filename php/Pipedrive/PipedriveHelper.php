<?php
/**
 * This base class aims to help you with PipedriveManager communication.
 * https://www.pipedrive.com
 *
 * @author  : Volodymyr Mon
 * @license : MIT
 */

namespace App;

class PipedriveHelper
{

    /**
     * Normalize url byt replacing some special symbols with their encoded analogues.
     *
     * @param string $route
     */
    public static function normalizeUrl(string &$route)
    {

        $route = str_replace(' ', '%20', $route);

    }

    /**
     * Append a url with PipedriveManager API token.
     *
     * @param string $route
     * @param string $token
     *
     * @return string
     */
    public static function appendUrlWithToken(string $route, string $token): string
    {

        /** @var string $connector */

        $connector = self::defineConnector($route);

        return "{$route}{$connector}api_token={$token}";

    }

    /**
     * Define connector for new attributes in the url.
     *
     * @param string $route
     *
     * @return string
     */
    public static function defineConnector(string $route) : string
    {

        return strpos($route, '?') === false ? '?' : '&';

    }

}