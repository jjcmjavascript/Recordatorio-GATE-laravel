<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Sistema\Accion;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('acciones', function ($user,$accion) {
            return array_has(session('permisos'), $accion);
        });

        Gate::define('any_accion', function($user, $json_acciones) {
            $permisos = array_keys(session('permisos')->toArray());
            return count(array_intersect($permisos, json_decode($json_acciones))) > 0;
        });

        Gate::define('acciones_ruta', function($user, $ruta) {
            $have = $user->perfiles()->whereHas('acciones', function($q) use ($ruta){
                $q->where('ruta', $ruta);
            })->first();

            return $have ? true : false;
        });

        Gate::define('buscar_accion', function($user, $accion, $id_seccion, $menu = null) {
            $query = $user->permisos()
            ->orderBy('acciones.nombre')
            ->groupBy('acciones.nombre')
            ->where('utl_seccion.id', $id_seccion)
            ->where('acciones.nombre', $accion);

            if ($menu) {
                $query->where('menus.nombre', $menu);
            }

            return !is_null($query->first());
        });
    }
}
