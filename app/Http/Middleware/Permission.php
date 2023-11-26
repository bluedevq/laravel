<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Permission
{
    protected array $skips = ['login'];

    public function handle(Request $request, Closure $next, ...$guards)
    {
        // get current controller, action from request
        $controller = getControllerName();
        $action = getActionName();

        // skip some of controllers: login,...
        if (in_array($controller, $this->skips)) {
            return $next($request);
        }

        // check controller, action in database
        $administrator = getGuard()->user();

        $roles = $administrator->roles->mapToGroups(function ($item) {
            return [$item->controller => $item->action];
        })->toArray();

        if (isset($roles[$controller]) && in_array($action, $roles[$controller])) {
            return $next($request);
        }

        session()->flash('action_failed', __('messages.not_permission'));

        return redirect()->route('admin.home');
    }
}
