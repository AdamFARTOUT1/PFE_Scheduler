<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Utils\FiliereColorHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     * Partage les couleurs de filières dynamiques dans toutes les vues via ViewComposer.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            try {
                if (Schema::hasTable('etudiants')) {
                    $filiereColors = FiliereColorHelper::getColors();
                    $filiereList   = FiliereColorHelper::getList();

                    $view->with('_filiereColors', $filiereColors);
                    $view->with('_filiereList', $filiereList);
                }
            } catch (\Exception $e) {
                // Silencieux si la base n'est pas encore disponible
                $view->with('_filiereColors', []);
                $view->with('_filiereList', []);
            }
        });
    }
}

