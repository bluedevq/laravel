@php
    $area = getArea();
    $controllerName = getControllerName();
@endphp

@if (isset($controllerName) && !empty($controllerName) && file_exists(public_path('assets/js/' . $area . '/autoload/' . $controllerName . '.js')))
    {{  script(asset('assets/js/' . $area . '/autoload/' . $controllerName . '.js')) }}
@endif

@if (isset($controllerName) && !empty($controllerName) && file_exists(public_path('assets/js/' . $area . '/webpack/' . $controllerName . '.js')))
    {{ script(asset('assets/js/' . $area . '/webpack/' . $controllerName . '.js')) }}
@endif
