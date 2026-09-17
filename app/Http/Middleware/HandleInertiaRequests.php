<?php

namespace App\Http\Middleware;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;


class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**+
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = auth()->user();
        $groupedModules = [];
        if ($user) {
            if ($user->isSystemAdmin()) {
                $modules = Module::all();
            } else {
                $modules = $user->modules()
                    ->where('is_active', 1)
                    ->with('parentModule')
                    ->get()
                    ->unique('id')
                    ->values();
            }

            $groupedModules = $modules->groupBy(function ($module) {
                return $module->parentModule ? $module->parentModule->name : 'General';
            })->map(function ($items, $parentname) {
                return [
                    'parent_name' => $parentname,
                    'items' => $items->map(function ($module) {
                        return [
                            'id' => $module->id,
                            'name' => $module->name,
                            'slug' => $module->slug,
                            'custom_route_link' => $module->custom_route_link
                        ];
                    })->values(),
                ];
            })->values();
        }
        //to get the list of modules the user has access

        $activeModuleRouteLink = $this->getActiveModule($request);
        if ($activeModuleRouteLink) {
            $currentModule = DB::table('modules')
                ->where('modules.custom_route_link', $activeModuleRouteLink)
                ->first();

            $currentModuleAccessDetails = DB::table('company_role_user')
                ->join('modules', 'company_role_user.module_id', '=', 'modules.id')
                ->join('roles', 'company_role_user.role_id', '=', 'roles.id')
                ->join('companies', 'company_role_user.company_id', '=', 'companies.id')
                ->where('company_role_user.user_id', $user->id)
                ->where(function ($query) use ($activeModuleRouteLink) {
                    $query->where('modules.custom_route_link', $activeModuleRouteLink);
                })
                ->select([
                    'companies.id as company_id',
                    'companies.name as company_name',
                    'roles.id as role_id',
                    'roles.code as role',
                    'modules.id as module_id',
                    'modules.name as module_name',
                    'modules.custom_route_link',
                ])->get();

            $request->attributes->set('moduleAccessDetails', $currentModuleAccessDetails);
            $request->attributes->set('moduleDetails', $currentModule);
        }



        return array_merge(parent::share($request), [
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            // ... your other shared props like auth
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'initials' => collect(preg_split('/[\s,]+/', $user->name))
                        ->map(fn($n) => mb_substr($n, 0, 1))
                        ->take(2)
                        ->join(''),
                    'employee_id' => $user->employee_id
                ] : null,
                'menuStructure' => $groupedModules,
            ]

        ]);
    }

    private function getActiveModule(Request $request): ?string
    {
        $routeName = "";
        if ($request->segment(1) === 'dashboard' && $request->segment(2))
            $routeName = $request->segment(2);

        return $routeName;
    }
}
