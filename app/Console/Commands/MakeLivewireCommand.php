<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeLivewireCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:livewire {name}
                            {--pagination : Enable pagination in the table}
                            {--filter : Enable filter section}
                            {--detail= : Enable and create detail modal (default name: Detail, or provide custom name)}
                            {--wizard : Scaffold a multi-step form instead of a standard form}
                            {--index= : Create index file (default name: Index, or provide custom name)}
                            {--form= : Create form file (default name: Form, or provide custom name)}
                            {--route : Create a new routes}
                            {--all : Create a full component with route}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Livewire component file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $studlyName = Str::studly($name);
        $pluralName = Str::plural($studlyName);
        $singularName = Str::singular($studlyName);
        $kebabName = Str::kebab($pluralName);

        // Check if model exists
        $modelPath = app_path("Models/{$singularName}.php");
        $modelExists = File::exists($modelPath);

        if (! $modelExists) {
            $this->error("❌ Model not found: {$singularName}");
            $this->warn("💡 Please create the model first using: php artisan make:model {$singularName}");

            if ($this->confirm('Do you want to create the model now?', true)) {
                $this->createFromStub('model', app_path('Models/'.$singularName.'.php'), [
                    'SINGULAR_CLASS' => $singularName,
                ]);
                $this->info("✅ Model created from custom stub!\n");
            } else {
                $this->warn("⚠️  Continuing without model...\n");
            }
        } else {
            $this->info("✓ Model found for {$singularName}\n");
        }

        // Check if migration exists (check regardless of model status)
        $migrationFiles = File::glob(database_path('migrations/*_create_'.Str::snake($pluralName).'_table.php'));

        if (empty($migrationFiles)) {
            $this->warn("⚠️  Migration not found for {$pluralName} table");

            if ($this->confirm('Do you want to create the migration now?', true)) {
                $tableName = Str::snake($pluralName);
                $datePrefix = date('Y_m_d_His');
                $migrationPath = database_path("migrations/{$datePrefix}_create_{$tableName}_table.php");
                $this->createFromStub('migration', $migrationPath, [
                    'TABLE_NAME' => $tableName,
                ]);
                $this->info("✅ Migration created from custom stub!\n");
            } else {
                $this->warn("⚠️  Continuing without migration...\n");
            }
        } else {
            $this->info("✓ Migration found for {$singularName}\n");
        }

        $all = $this->option('all');
        $paginationOption = $all ? true : $this->option('pagination');
        $filterOption = $all ? true : $this->option('filter');
        $routeOption = $all ? true : $this->option('route');

        // Get raw option values and check if they were passed
        $rawIndexOption = $this->option('index');
        $rawFormOption = $this->option('form');
        $rawDetailOption = $this->option('detail');

        // Check if option was actually passed in the command
        $indexWasProvided = in_array('--index', $_SERVER['argv']) ||
                           count(array_filter($_SERVER['argv'], fn ($arg) => str_starts_with($arg, '--index='))) > 0;
        $formWasProvided = in_array('--form', $_SERVER['argv']) ||
                          count(array_filter($_SERVER['argv'], fn ($arg) => str_starts_with($arg, '--form='))) > 0;
        $detailWasProvided = in_array('--detail', $_SERVER['argv']) ||
                            count(array_filter($_SERVER['argv'], fn ($arg) => str_starts_with($arg, '--detail='))) > 0;
        $wizardOption = $this->option('wizard');

        // Determine index option behavior
        switch (true) {
            case $all:
                $indexOption = 'Index';
                break;
            case $indexWasProvided && $rawIndexOption:
                // Has argument: --index=CustomName
                $indexOption = Str::studly($rawIndexOption);
                break;
            case $indexWasProvided:
                // No argument: --index
                $indexOption = 'Index';
                break;
            default:
                // Option not provided
                $indexOption = false;
                break;
        }

        // Determine form option behavior
        switch (true) {
            case $all:
                $formOption = 'Form';
                break;
            case $formWasProvided && $rawFormOption:
                // Has argument: --form=CustomName
                $formOption = Str::studly($rawFormOption);
                break;
            case $formWasProvided:
                // No argument: --form
                $formOption = 'Form';
                break;
            default:
                // Option not provided
                $formOption = false;
                break;
        }

        // Determine detail option behavior
        switch (true) {
            case $all:
                $detailOption = 'Detail';
                break;
            case $detailWasProvided && $rawDetailOption:
                // Has argument: --detail=CustomName
                $detailOption = Str::studly($rawDetailOption);
                break;
            case $detailWasProvided:
                // No argument: --detail
                $detailOption = 'Detail';
                break;
            default:
                // Option not provided
                $detailOption = false;
                break;
        }

        // If neither --index nor --form is specified, create both with default names
        if (! $indexWasProvided && ! $formWasProvided && ! $detailWasProvided && ! $all) {
            $indexOption = 'Index';
            $formOption = 'Form';
            $detailOption = 'Detail';
        }

        $createBoth = $indexOption && $formOption;

        $this->info("Creating livewire component: {$pluralName}");
        $this->info('Features: '.($all ? '✓ ALL FEATURES ' : '').
                                   ($createBoth ? '✓ Index & Form ' :
                                   ($indexOption && ! $formOption ? "✓ {$indexOption} " : '').
                                   ($formOption && ! $indexOption ? "✓ {$formOption} " : '')).
                                   ($paginationOption ? '✓ Pagination ' : '').
                                   ($filterOption ? '✓ Filter ' : '').
                                   ($routeOption ? '✓ Route ' : '').
                                   ($detailOption ? "✓ {$detailOption} " : ''));

        // Create the Livewire component with custom names
        $livewireIndex = $indexOption ? $pluralName.'/'.$indexOption : null;
        $livewireForm = $formOption ? $pluralName.'/'.$formOption : null;

        if ($livewireIndex) {
            $this->newLine();
            $this->call('make:livewire', [
                'name' => $livewireIndex,
            ]);
        }

        if ($livewireForm) {
            $this->newLine();
            $this->call('make:livewire', [
                'name' => $livewireForm,
            ]);
        }

        // Convert name to folder path
        $classFolderPath = str_replace('.', '/', $pluralName);
        $viewFolderPath = str_replace('.', '/', $kebabName);
        $indexClassPath = $livewireIndex ? app_path('Livewire/'.$classFolderPath.'/'.$indexOption.'.php') : null;
        $indexViewPath = $livewireIndex ? resource_path('views/livewire/'.$viewFolderPath.'/'.Str::kebab($indexOption).'.blade.php') : null;
        $formClassPath = $livewireForm ? app_path('Livewire/'.$classFolderPath.'/'.$formOption.'.php') : null;
        $formViewPath = $livewireForm ? resource_path('views/livewire/'.$viewFolderPath.'/'.Str::kebab($formOption).'.blade.php') : null;
        $detailClassPath = $detailOption ? app_path('Livewire/'.$classFolderPath.'/'.$detailOption.'.php') : null;
        $detailViewPath = $detailOption ? resource_path('views/livewire/'.$viewFolderPath.'/'.Str::kebab($detailOption).'.blade.php') : null;

        // Create index view if --index is specified or if creating both
        if ($indexClassPath) {
            $this->createFromStub('class/index', $indexClassPath, [
                'PLURAL_CLASS' => $pluralName,
                'PLURAL_CLASS_LOWER' => Str::camel($pluralName),
                'SINGULAR_CLASS' => $singularName,
                'SINGULAR_TITLE' => $singularName,
                'SINGULAR_CLASS_LOWER' => Str::camel($singularName),
                'KEBAB_CLASS' => $kebabName,
                'HAS_PAGINATION' => $paginationOption,
                'HAS_FILTER' => $filterOption,
                'INDEX_CLASS' => $indexOption ?: 'Index',
                'INDEX_NAME' => Str::kebab($indexOption),
                'FORM_NAME' => Str::kebab($formOption),
            ]);

            $this->createFromStub('view/index', $indexViewPath, [
                'PLURAL_TITLE' => $pluralName,
                'PLURAL_CLASS_LOWER' => Str::camel($pluralName),
                'SINGULAR_TITLE' => $singularName,
                'SINGULAR_CLASS_LOWER' => Str::camel($singularName),
                'KEBAB_CLASS' => $kebabName,
                'INDEX_NAME' => Str::kebab($indexOption),
                'FORM_NAME' => Str::kebab($formOption),
                'DETAIL_NAME' => Str::kebab($detailOption),
                'HAS_PAGINATION' => $paginationOption ? 'true' : 'false',
                'HAS_FILTER' => $filterOption ? 'true' : 'false',
                'HAS_DETAIL' => $detailOption ? 'true' : 'false',
                'PAGINATION_ATTR' => $paginationOption ? ':pagination="true"' : '',
            ]);

            $this->createFromStub('view/components/action-button', resource_path('views/livewire/'.$viewFolderPath.'/components/action-button.blade.php'), [
                'SINGULAR_TITLE' => $singularName,
                'KEBAB_CLASS' => $kebabName,
                'SINGULAR_CLASS_LOWER' => Str::camel($singularName),
                'INDEX_NAME' => Str::kebab($indexOption),
                'FORM_NAME' => Str::kebab($formOption),
                'DETAIL_NAME' => Str::kebab($detailOption),
                'HAS_DETAIL' => $detailOption ? 'true' : 'false',
            ]);

            $this->createFromStub('view/components/status-modal', resource_path('views/livewire/'.$viewFolderPath.'/components/status-modal.blade.php'), [
                'SINGULAR_TITLE' => $singularName,
                'SINGULAR_CLASS_LOWER' => Str::camel($singularName),
            ]);
        }

        // Create form view if --form is specified or if creating both
        if ($formClassPath) {
            $wizardOption = $this->option('wizard');

            if ($wizardOption) {
                // Wizard Top-Level Wrapper
                $this->createFromStub('class/wizard/form', $formClassPath, [
                    'PLURAL_CLASS' => $pluralName,
                    'SINGULAR_CLASS' => $singularName,
                    'SINGULAR_CLASS_LOWER' => Str::camel($singularName),
                    'KEBAB_CLASS' => $kebabName,
                    'FORM_CLASS' => $formOption ?: 'Form',
                    'FORM_NAME' => Str::kebab($formOption),
                    'INDEX_NAME' => Str::kebab($indexOption),
                ]);

                $this->createFromStub('view/wizard/form', $formViewPath, [
                    'SINGULAR_TITLE' => $singularName,
                    'KEBAB_CLASS' => $kebabName,
                    'FORM_NAME' => Str::kebab($formOption),
                    'INDEX_NAME' => Str::kebab($indexOption),
                    'DETAILS_VIEW' => Str::kebab($singularName).'-details',
                ]);

                // Wizard Details Sub-Component
                $detailsClassName = $singularName.'Details';
                $detailsViewName = Str::kebab($singularName).'-details';

                $detailsClassPath = app_path('Livewire/'.$classFolderPath.'/'.$detailsClassName.'.php');
                $detailsViewPath = resource_path('views/livewire/'.$viewFolderPath.'/'.$detailsViewName.'.blade.php');

                $this->createFromStub('class/wizard/details', $detailsClassPath, [
                    'PLURAL_CLASS' => $pluralName,
                    'SINGULAR_CLASS' => $singularName,
                    'SINGULAR_CLASS_LOWER' => Str::camel($singularName),
                    'KEBAB_CLASS' => $kebabName,
                    'FORM_CLASS' => $formOption ?: 'Form',
                    'FORM_NAME' => Str::kebab($formOption),
                    'INDEX_NAME' => Str::kebab($indexOption),
                    'DETAILS_CLASS' => $detailsClassName,
                    'DETAILS_VIEW' => $detailsViewName,
                ]);

                $this->createFromStub('view/wizard/details', $detailsViewPath, [
                    'SINGULAR_TITLE' => $singularName,
                    'KEBAB_CLASS' => $kebabName,
                    'FORM_NAME' => Str::kebab($formOption),
                    'INDEX_NAME' => Str::kebab($indexOption),
                    'SINGULAR_CLASS_LOWER' => Str::camel($singularName),
                    'DETAILS_VIEW' => $detailsViewName,
                ]);

                // Wizard Preview Sub-Component
                $previewClassPath = app_path('Livewire/'.$classFolderPath.'/Preview.php');
                $previewViewPath = resource_path('views/livewire/'.$viewFolderPath.'/preview.blade.php');

                $this->createFromStub('class/wizard/preview', $previewClassPath, [
                    'PLURAL_CLASS' => $pluralName,
                    'SINGULAR_CLASS' => $singularName,
                    'SINGULAR_TITLE' => $singularName,
                    'SINGULAR_CLASS_LOWER' => Str::camel($singularName),
                    'KEBAB_CLASS' => $kebabName,
                    'FORM_CLASS' => $formOption ?: 'Form',
                    'FORM_NAME' => Str::kebab($formOption),
                    'INDEX_NAME' => Str::kebab($indexOption),
                ]);

                $this->createFromStub('view/wizard/preview', $previewViewPath, [
                    'SINGULAR_TITLE' => $singularName,
                    'KEBAB_CLASS' => $kebabName,
                    'FORM_NAME' => Str::kebab($formOption),
                    'INDEX_NAME' => Str::kebab($indexOption),
                    'SINGULAR_CLASS_LOWER' => Str::camel($singularName),
                ]);
            } else {
                $this->createFromStub('class/form', $formClassPath, [
                    'PLURAL_CLASS' => $pluralName,
                    'SINGULAR_CLASS' => $singularName,
                    'SINGULAR_TITLE' => $singularName,
                    'SINGULAR_CLASS_LOWER' => Str::camel($singularName),
                    'KEBAB_CLASS' => $kebabName,
                    'FORM_CLASS' => $formOption ?: 'Form',
                    'FORM_NAME' => Str::kebab($formOption),
                    'INDEX_NAME' => Str::kebab($indexOption),
                ]);

                $this->createFromStub('view/form', $formViewPath, [
                    'SINGULAR_TITLE' => $singularName,
                    'KEBAB_CLASS' => $kebabName,
                    'FORM_NAME' => Str::kebab($formOption),
                    'INDEX_NAME' => Str::kebab($indexOption),
                ]);
            }
        }

        // Create detail view if --detail is specified and index exists
        if ($detailViewPath) {
            $this->createFromStub('class/detail', $detailClassPath, [
                'PLURAL_CLASS' => $pluralName,
                'SINGULAR_CLASS' => $singularName,
                'SINGULAR_CLASS_LOWER' => Str::camel($singularName),
                'KEBAB_CLASS' => $kebabName,
                'DETAIL_CLASS' => $detailOption ?: 'Detail',
                'DETAIL_NAME' => Str::kebab($detailOption),
                'INDEX_NAME' => Str::kebab($indexOption),
            ]);

            $this->createFromStub('view/detail', $detailViewPath, [
                'SINGULAR_TITLE' => $singularName,
                'KEBAB_CLASS' => $kebabName,
                'INDEX_NAME' => Str::kebab($indexOption),
                'FORM_NAME' => Str::kebab($formOption),
                'SINGULAR_CLASS_LOWER' => Str::camel($singularName),
            ]);
        }

        if ($createBoth || $all) {
            $this->createFromStub('enum', app_path('Enums/'.$singularName.'Status.php'), [
                'SINGULAR_CLASS' => $singularName,
            ]);
            $this->createFromStub('repository', app_path('Repositories/'.$singularName.'Repository.php'), [
                'SINGULAR_CLASS' => $singularName,
            ]);
            $this->createFromStub('service', app_path('Services/'.$singularName.'Service.php'), [
                'SINGULAR_CLASS' => $singularName,
            ]);
            $this->createFromStub('rules', app_path('Rules/'.$singularName.'Rules.php'), [
                'SINGULAR_CLASS' => $singularName,
            ]);
        }

        if ($routeOption) {
            $routeStub = 'livewire/routes';
            $this->addRoutesFromStub($routeStub, [
                'PLURAL_TITLE' => $pluralName,
                'PLURAL_CLASS' => $pluralName,
                'SINGULAR_CLASS' => $singularName,
                'KEBAB_CLASS' => $kebabName,
                'INDEX_CLASS' => $indexOption ?: 'Index',
                'FORM_CLASS' => $formOption ?: 'Form',
                'DETAIL_CLASS' => $detailOption ?: 'Detail',
                'INDEX_NAME' => Str::kebab($indexOption),
                'FORM_NAME' => Str::kebab($formOption),
                'DETAIL_NAME' => Str::kebab($detailOption),
            ]);
        }

        $this->info("\n✅ Livewire Component '{$pluralName}' created successfully!");
        $this->displaySummary($singularName, $indexOption, $formOption, $paginationOption, $filterOption, $routeOption, $detailOption, ($createBoth || $all));
        // return Command::SUCCESS;
    }

    protected function createFromStub($stubName, $destinationPath, $replacements)
    {
        $stubPath = base_path("stub/livewire/{$stubName}.stub");
        $stubContent = File::get($stubPath);

        foreach ($replacements as $key => $value) {
            $stubContent = str_replace('{{'.$key.'}}', $value, $stubContent);
        }

        // Ensure the directory exists
        if (! is_dir(dirname($destinationPath))) {
            mkdir(dirname($destinationPath), 0755, true);
        }

        File::put($destinationPath, $stubContent);
    }

    protected function addRoutesFromStub($stubName, $replacements = [])
    {
        $stubPath = base_path("stub/{$stubName}.stub");

        if (! File::exists($stubPath)) {
            $this->error("Routes stub file not found: {$stubPath}");

            return false;
        }

        $routePath = base_path('routes/web.php');
        $existingContent = File::get($routePath);
        $stubContent = File::get($stubPath);

        // Replace placeholders
        foreach ($replacements as $key => $value) {
            $stubContent = str_replace('{{'.$key.'}}', $value, $stubContent);
        }

        // Extract import statements
        $pluralClass = $replacements['PLURAL_CLASS'];
        $indexClass = $replacements['INDEX_CLASS'] ?? 'Index';
        $formClass = $replacements['FORM_CLASS'] ?? 'Form';

        $importStatements = [
            "use App\\Livewire\\{$pluralClass}\\{$indexClass} as {$pluralClass}{$indexClass};",
            "use App\\Livewire\\{$pluralClass}\\{$formClass} as {$pluralClass}{$formClass};",
        ];

        if (!empty($replacements['DETAIL_CLASS'])) {
            $detailClass = $replacements['DETAIL_CLASS'];
            $importStatements[] = "use App\\Livewire\\{$pluralClass}\\{$detailClass} as {$pluralClass}{$detailClass};";
        }

        // Check if route already exists by looking for the kebab-case route pattern
        $kebabClass = $replacements['KEBAB_CLASS'];

        // Pattern to match existing route block for this resource
        $pattern = '/\/\/ '.preg_quote($replacements['PLURAL_CLASS'], '/').' Routes.*?(?=\/\/|\z)/s';

        if (preg_match($pattern, $existingContent)) {
            // Replace existing routes
            $newContent = preg_replace($pattern, trim($stubContent), $existingContent);
            File::put($routePath, $newContent);
            $this->info("✅ Routes for '{$kebabClass}' updated in web.php");
        } elseif (Str::contains($existingContent, "/{$kebabClass}")) {
            // Route exists but without comment block - just ignore
            $this->warn("⚠️  Routes for '{$kebabClass}' already exist in web.php - skipping");

            return false;
        } else {
            // Add import statements at the top if they don't exist
            foreach ($importStatements as $import) {
                if (! Str::contains($existingContent, $import)) {
                    // Find the last 'use' statement and add after it
                    $existingContent = preg_replace(
                        '/(use [^;]+;)(?!.*use )/s',
                        "$1\n{$import}",
                        $existingContent
                    );
                }
            }

            // Route doesn't exist - append new routes
            $existingContent .= "\n".$stubContent;
            File::put($routePath, $existingContent);
            $this->info('✅ Routes and imports added to web.php');
        }

        return true;
    }

    protected function displaySummary($singularName, $indexOption, $formOption, $paginationOption, $filterOption, $routeOption, $detailOption, $hasAppLogic = false)
    {
        $this->newLine();
        $this->info('📁 Files Created:');

        if ($indexOption && $formOption) {
            $this->line("   - Livewire Component: {$formOption}.php & {$indexOption}.php");
            $this->line('   - View: '.Str::kebab($indexOption).'.blade.php & '.Str::kebab($formOption).'.blade.php');
        } elseif ($indexOption) {
            $this->line("   - Livewire Component: {$indexOption}.php");
            $this->line('   - View: '.Str::kebab($indexOption).'.blade.php');
        } elseif ($formOption) {
            $this->line("   - Livewire Component: {$formOption}.php");
            $this->line('   - View: '.Str::kebab($formOption).'.blade.php');
        } elseif ($detailOption) {
            $this->line('   - View: '.Str::kebab($detailOption).'.blade.php');
        }

        if ($hasAppLogic) {
            $this->line("   - Backend: {$singularName}Repository, Service, Rules, Enum");
            $this->line("   - Component: action-button.blade.php & status-modal.blade.php");
        }

        $this->newLine();
        $this->info('⚙️  Features Enabled:');
        $this->line('   '.($paginationOption ? '✓' : '✗').' Pagination');
        $this->line('   '.($filterOption ? '✓' : '✗').' Filter');
        $this->line('   '.($routeOption ? '✓' : '✗').' Route');
        $this->line('   '.($detailOption ? '✓' : '✗')." {$detailOption} Page");
        $this->line('   '.($this->option('wizard') ? '✓' : '✗')." Wizard Form Layout");

        $this->newLine();
        if (! $this->option('route') && ! $this->option('all')) {
            $this->warn('   💡 Add --route flag to auto-generate routes');
        }

        $this->warn("   💡 Make sure to have model and migration created for {$singularName} ");
    }
}
