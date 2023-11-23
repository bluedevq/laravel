<?php

namespace App\Base\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;

class BaseController extends Controller
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    protected array $viewData;

    protected string $title;

    protected $repository;

    protected $validator;

    protected $service;

    public function __construct()
    {
        $this->title = '';
        $this->viewData = [];
    }

    public function setViewData(array $data)
    {
        $this->viewData = array_merge($this->viewData, $data);
    }

    public function getViewData($item = null)
    {
        if (!is_null($item)) {
            return data_get($this->viewData, $item);
        }

        return $this->viewData;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function render($view = null, array $data = [], array $mergeData = [])
    {
        $area = getArea();
        $tmp = !empty($area) ? $area . '.' : '';
        $actionName = getActionName();
        $view = str($tmp)->append(!empty($view) ? $view : getControllerName() . '.' . $actionName);
        $routePrefix = str_replace('.' . $actionName, '', Route::currentRouteName());

        $data = array_merge($data, $this->getViewData(), [
            'title' => $this->getTitle(),
            'routePrefix' => $routePrefix,
        ]);

        return view($view, $data, $mergeData);
    }
}
