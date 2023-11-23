<?php

namespace App\Base\Providers\Collective;

use Collective\Html\HtmlBuilder;

class BaseHtmlBuilder extends HtmlBuilder
{
    public function script($url, $attributes = [], $secure = null)
    {
        $attributes['src'] = buildVersion($this->url->asset($url, $secure));

        return $this->toHtmlString('<script' . $this->attributes($attributes) . '></script>');
    }

    public function style($url, $attributes = [], $secure = null)
    {
        $defaults = ['media' => 'all', 'type' => 'text/css', 'rel' => 'stylesheet'];
        $attributes = array_merge($defaults, $attributes);
        $attributes['href'] = buildVersion($this->url->asset($url, $secure));

        return $this->toHtmlString('<link' . $this->attributes($attributes) . '>');
    }

    public function image($url, $alt = null, $attributes = [], $secure = null)
    {
        $attributes['alt'] = $alt;

        return $this->toHtmlString('<img src="' . buildVersion($this->url->asset($url, $secure)) . '"' . $this->attributes($attributes) . '>');
    }
}
