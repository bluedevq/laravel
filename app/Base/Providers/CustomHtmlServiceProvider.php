<?php

namespace App\Base\Providers;

use Collective\Html\HtmlServiceProvider;
use App\Base\Providers\Collective\BaseFormBuilder;
use App\Base\Providers\Collective\BaseHtmlBuilder;

class CustomHtmlServiceProvider extends HtmlServiceProvider
{
    protected function registerFormBuilder()
    {
        $this->app->singleton('form', function ($app) {
            $form = new BaseFormBuilder($app['html'], $app['url'], $app['view'], $app['session.store']->token(), $app['request']);

            return $form->setSessionStore($app['session.store']);
        });
    }

    protected function registerHtmlBuilder()
    {
        $this->app->singleton('html', function ($app) {
            return new BaseHtmlBuilder($app['url'], $app['view']);
        });
    }

    public function provides()
    {
        return ['html', 'form', BaseHtmlBuilder::class, BaseFormBuilder::class];
    }
}
