<?php

namespace App\Console\Commands;

use Exception;
use App\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class PermissionDump extends Command
{
    protected $signature = 'permission:dump';

    protected $description = 'Auto create permission';

    /**
     * @var array
     */
    private $permissionIds = [];

    /**
     * @var array
     */
    private $updatePermissionIds = [];

    public function handle()
    {
        $this->info('---------------------------------');
        $this->info('Start dumping permission to Database');

        try {
            DB::beginTransaction();

            /** @var RouteCollection $routes */
            $routes = Route::getRoutes();

            if (!empty($routes)) {
                /** @var Route $route */
                foreach ($routes as $route) {
                    $middleware = $this->getRouteMiddleware($route);
                    if (in_array('can', $middleware)) {
                        $this->savePermission($route);
                    }
                }
            }

            $deletePermissionIds = array_merge($this->permissionIds, $this->updatePermissionIds);
            $deletePermission = Permission::whereNotIn('id', $deletePermissionIds)->delete();

            DB::commit();
            $this->info('--');
            $this->info('Created ' . count($this->permissionIds) . ' permissions');
            $this->info('Updated ' . count($this->updatePermissionIds) . ' permissions');
            $this->info('Deleted ' . $deletePermission . ' permissions');

            $this->info('Completed dumping permission to Database');
        } catch (Exception $exception) {
            $this->info('Fail to dump permission to Database');
            $this->info($exception->getMessage());
            $this->info($exception->getFile());
            $this->info($exception->getLine());
            DB::rollback();
        }

        $this->info('---------------------------------');
    }

    protected function getRouteMiddleware($router)
    {
        $middleware = $this->getRouteAction($router, 'middleware');
        if (null == $middleware) {
            $middleware = [];
        }

        return $middleware;
    }

    protected function getRouteAction($router, $action)
    {
        if (is_array($router)) {
            if (isset($router['action'][$action])) {
                return $router['action'][$action];
            }
        }

        return $router->getAction($action);
    }

    protected function getRoutePrefix($router)
    {
        $prefix = $this->getRouteAction($router, 'prefix');
        if (empty($prefix) && is_array($router)) {
            $prefix = $this->getRouteAction($router, 'group');
        }
        return $prefix;
    }

    protected function getRouteMethods($router)
    {
        if (is_array($router)) {
            $method = $router['method'];
            if (!is_array($method)) {
                $method = [$method];
            }
            return $method;
        } else {
            return $router->methods();
        }
    }

    protected function getRouteUri($router)
    {
        if (is_array($router)) {
            return $router['uri'];
        } else {
            return $router->uri();
        }
    }

    private function savePermission($route)
    {
        $as = $this->getRouteAction($route, 'as');

        $dependencies = $this->getRouteAction($route, 'dependencies');

        $methods = $this->getRouteMethods($route);
        $method = strtolower(array_shift($methods));

        $per_name = $this->getRouteUri($route) . '-' . $method;
        $display_name = str_replace('.', ' ', $as);

        $data = [
            'name' => $per_name,
            'alias' => $as,
            'display_name' => ucfirst($display_name),
            'dependencies' => $dependencies,
        ];

        $per = Permission::where('name', $per_name)->first();

        if ($per) {
            $per->deleted_at = null;
            $per->dependencies = $dependencies;
            $per->display_name = ucfirst($display_name);
            if (!empty($per->alias)) {
                $per->alias .= ',';
            }
            $per->alias .= $as;
            if ($per->save()) {
                array_push($this->updatePermissionIds, $per->id);
            }
        } else {
            $per = Permission::create($data);
            if ($per) {
                array_push($this->permissionIds, $per->id);
            }
        }
    }
}
