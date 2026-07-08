<?php

namespace App\Http\Middleware;

use App\Models\Module;
use Illuminate\Http\Request;
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
            if($user->isSystemAdmin()){
                $modules = Module::all();

            }else{
                $modules = $user->companyRoleAssignments()
                    ->with('role.modules.parentModule')
                    ->get()
                    ->pluck('role.modules')// get only the modules - nested array
                    ->flatten()
                    ->unique('id'); // remove the duplicates
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
                ] : null,
                'menuStructure' => $groupedModules,
            ]

        ]);
    }
}
