<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Module;

class CreateStockModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:create-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crée le module Gestion de Stock s\'il n\'existe pas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Vérification du module Gestion de Stock...');

        $module = Module::where('slug', 'stock_management')->first();

        if ($module) {
            $this->info("✅ Le module existe déjà (ID: {$module->id})");
            $this->info("   Nom: {$module->name}");
            $this->info("   Statut: " . ($module->is_active ? 'Actif' : 'Inactif'));
            $this->info("   Optionnel: " . ($module->is_optional ? 'Oui' : 'Non'));

            if ($module->is_optional && $module->price) {
                $this->info("   Prix: " . number_format($module->price, 0, ',', ' ') . " {$module->currency}");
            }

            return 0;
        }

        $this->info('📦 Création du module Gestion de Stock...');

        $module = Module::create([
            'name' => 'Gestion de Stock',
            'slug' => 'stock_management',
            'description' => 'Gestion complète du stock : produits, catégories, inventaire',
            'icon' => 'ti-warehouse',
            'category' => 'premium',
            'is_active' => true,
            'is_optional' => true,
            'price' => null,
            'currency' => 'XOF',
            'sort_order' => 22,
            'routes' => ['stock.index', 'stock.products', 'stock.categories'],
        ]);

        $this->info("✅ Module créé avec succès (ID: {$module->id})");
        $this->info("   Nom: {$module->name}");
        $this->info("   Slug: {$module->slug}");
        $this->info("   Catégorie: {$module->category}");
        $this->info("   Optionnel: Oui");
        $this->info("   Statut: Actif");

        return 0;
    }
}

