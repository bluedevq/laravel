<?php

namespace App\Base\Helpers;

use Illuminate\Support\Facades\URL as FacadesURL;

class Url
{
    protected static $currentControllerName = null;

    protected static $instance = null;

    protected $old = 0;

    public const URl_KEY = 'url_key';

    public const QUERY = '_o';

    public const OLD_QUERY = '_o_';

    public const BACK_URL_LIMIT = 200;

    public static function getInstance()
    {
        if (!static::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function getCurrentControllerName()
    {
        return self::$currentControllerName;
    }

    public static function setCurrentControllerName($currentControllerName)
    {
        self::$currentControllerName = $currentControllerName;
    }

    public static function setInstance($instance)
    {
        self::$instance = $instance;
    }

    public static function genUrlKey($default = '', $params = [])
    {
        $url = static::getFullUrl($default, $params);
        $urlKeys = session(self::URl_KEY, []);
        global $urlIdx;
        $urlIdx++;
        $time = time() . $urlIdx;
        krsort($urlKeys, SORT_STRING);

        if (!empty($urlKeys)) {
            $limit = self::BACK_URL_LIMIT;
            $urlKeys = array_chunk($urlKeys, $limit - 1, true);
            $urlKeys = $urlKeys[0];
        }

        $urlKeys[$time] = $url;
        session([self::URl_KEY => $urlKeys]);

        return $time;
    }

    protected static function getFullUrl($default = '', $params = [])
    {
        if ($default) {
            $url = str_contains($default, '.') ? route($default, $params) : $default;
            $url = parse_url($url);
            $r = $url['path'] ?? '';

            return isset($url['query']) && $r ? $r . '?' . $url['query'] : $r;
        }

        $router = app('router');
        $request = request()->all();
        $inputs = static::buildParamString($request);
        $uri = $router->getCurrentRoute()->uri;

        foreach ($router->getCurrentRoute()->parameters as $parameter => $value) {
            $uri = str_replace('{' . $parameter . '}', $value, $uri);
        }

        return $uri . $inputs;
    }

    protected static function buildParamString($params, $params1 = [])
    {
        $params = array_merge($params1, $params);
        $params = http_build_query($params);

        return $params ? '?' . $params : '';
    }

    public static function getBackUrl($full = true, $defaultUrl = '', $recursive = false)
    {
        $old = request()->get(self::QUERY, false);

        if (!$old) {
            return !empty($defaultUrl) ? $defaultUrl : url()->previous();
        }

        $urlKeys = session(self::URl_KEY, []);
        $url = $urlKeys[$old] ?? $defaultUrl;

        if ($recursive) {
            return self::backRecursive($url, $full, $defaultUrl);
        }

        return $full ? url($url) : $url;
    }

    protected static function backRecursive($url, $full, $defaultUrl)
    {
        $parse = parse_url($url);
        $params = data_get($parse, 'query');
        parse_str($params, $params);

        if (data_get($params, self::QUERY)) {
            $old = data_get($params, self::QUERY);
            $urlKeys = session(self::URl_KEY, []);

            $url = data_get($urlKeys, $old, $defaultUrl);
        }

        return $full ? url($url) : $url;
    }

    public static function backUrl($url, $params = [], $default = '', $paramsDefault = [])
    {
        $old = self::genUrlKey($default, $paramsDefault);
        $params = array_merge((array)$params, [self::QUERY => $old]);

        if (str_contains($url, '/')) {
            return url($url, $params);
        }

        return route($url, $params);
    }

    protected static function getOldKey()
    {
        return static::getCurrentControllerName() . self::OLD_QUERY;
    }

    public static function getOldUrl()
    {
        return session(static::getOldKey(), '');
    }

    public static function collectOldUrl()
    {
        session([static::getOldKey() => FacadesURL::previous()]);
    }

    public static function keepBackUrl($value = null)
    {
        $value = $value ? $value : request()->get(self::QUERY, '');

        return '<input type="hidden" name="' . self::QUERY . '" value="' . $value . '">';
    }
}
