<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RagWebsite;
use Illuminate\Support\Str;

class ManageRagWebsites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rag:manage
                           {action : Action to perform (list|add|update|delete|enable|disable|show)}
                           {--url= : Website URL}
                           {--name= : Website name}
                           {--description= : Website description}
                           {--id= : Website ID for specific actions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage RAG websites (list, add, update, delete, enable, disable)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'list':
                return $this->listWebsites();
            case 'add':
                return $this->addWebsite();
            case 'update':
                return $this->updateWebsite();
            case 'delete':
                return $this->deleteWebsite();
            case 'enable':
                return $this->toggleWebsite(true);
            case 'disable':
                return $this->toggleWebsite(false);
            case 'show':
                return $this->showWebsite();
            default:
                $this->error("Unknown action: {$action}");
                $this->line('Available actions: list, add, update, delete, enable, disable, show');
                return Command::FAILURE;
        }
    }

    private function listWebsites()
    {
        $websites = RagWebsite::orderBy('created_at', 'desc')->get();

        if ($websites->isEmpty()) {
            $this->warn('No websites found.');
            return Command::SUCCESS;
        }

        $this->info("📋 RAG Websites ({$websites->count()} total)");
        $this->newLine();

        $headers = ['ID', 'Name', 'URL', 'Status', 'Last Scraped', 'Content Length'];
        $rows = [];

        foreach ($websites as $website) {
            $rows[] = [
                $website->id,
                Str::limit($website->name, 30),
                Str::limit($website->url, 50),
                $website->is_active ? '✅ Active' : '❌ Inactive',
                $website->last_scraped_at ? $website->last_scraped_at->format('Y-m-d H:i') : 'Never',
                $website->content ? number_format(strlen($website->content)) . ' chars' : 'No content'
            ];
        }

        $this->table($headers, $rows);

        return Command::SUCCESS;
    }

    private function addWebsite()
    {
        $url = $this->option('url') ?: $this->ask('Website URL');
        $name = $this->option('name') ?: $this->ask('Website name');
        $description = $this->option('description') ?: $this->ask('Website description (optional)', '');

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->error('Invalid URL format.');
            return Command::FAILURE;
        }

        // Check if URL already exists
        if (RagWebsite::where('url', $url)->exists()) {
            $this->error('Website with this URL already exists.');
            return Command::FAILURE;
        }

        try {
            $website = RagWebsite::create([
                'name' => $name,
                'url' => $url,
                'description' => $description,
                'is_active' => true,
                'metadata' => [
                    'added_by' => 'command',
                    'created_at' => now()->toISOString()
                ]
            ]);

            $this->info("✅ Website '{$name}' added successfully! (ID: {$website->id})");
            $this->line("💡 Run 'php artisan scrape:website {$url}' to scrape content.");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Failed to add website: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    private function updateWebsite()
    {
        $id = $this->option('id') ?: $this->ask('Website ID');
        $website = RagWebsite::find($id);

        if (!$website) {
            $this->error("Website with ID {$id} not found.");
            return Command::FAILURE;
        }

        $this->info("Updating: {$website->name}");

        $name = $this->option('name') ?: $this->ask('Website name', $website->name);
        $url = $this->option('url') ?: $this->ask('Website URL', $website->url);
        $description = $this->option('description') ?: $this->ask('Website description', $website->description);

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->error('Invalid URL format.');
            return Command::FAILURE;
        }

        // Check if new URL conflicts with other websites
        $conflicting = RagWebsite::where('url', $url)->where('id', '!=', $id)->exists();
        if ($conflicting) {
            $this->error('Another website with this URL already exists.');
            return Command::FAILURE;
        }

        try {
            $website->update([
                'name' => $name,
                'url' => $url,
                'description' => $description,
                'metadata' => array_merge($website->metadata ?? [], [
                    'updated_by' => 'command',
                    'updated_at' => now()->toISOString()
                ])
            ]);

            $this->info("✅ Website updated successfully!");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Failed to update website: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    private function deleteWebsite()
    {
        $id = $this->option('id') ?: $this->ask('Website ID');
        $website = RagWebsite::find($id);

        if (!$website) {
            $this->error("Website with ID {$id} not found.");
            return Command::FAILURE;
        }

        $this->warn("⚠️  This will permanently delete: {$website->name}");
        
        if (!$this->confirm('Are you sure you want to delete this website?')) {
            $this->info('Deletion cancelled.');
            return Command::SUCCESS;
        }

        try {
            $websiteName = $website->name;
            $website->delete();
            
            $this->info("✅ Website '{$websiteName}' deleted successfully!");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Failed to delete website: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    private function toggleWebsite($active)
    {
        $id = $this->option('id') ?: $this->ask('Website ID');
        $website = RagWebsite::find($id);

        if (!$website) {
            $this->error("Website with ID {$id} not found.");
            return Command::FAILURE;
        }

        $action = $active ? 'enabled' : 'disabled';
        $status = $active ? '✅ Active' : '❌ Inactive';

        try {
            $website->update([
                'is_active' => $active,
                'metadata' => array_merge($website->metadata ?? [], [
                    'status_changed_by' => 'command',
                    'status_changed_at' => now()->toISOString()
                ])
            ]);

            $this->info("✅ Website '{$website->name}' {$action} successfully! Status: {$status}");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Failed to update website status: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    private function showWebsite()
    {
        $id = $this->option('id') ?: $this->ask('Website ID');
        $website = RagWebsite::find($id);

        if (!$website) {
            $this->error("Website with ID {$id} not found.");
            return Command::FAILURE;
        }

        $this->info("📄 Website Details");
        $this->newLine();

        $details = [
            ['Property', 'Value'],
            ['ID', $website->id],
            ['Name', $website->name],
            ['URL', $website->url],
            ['Description', $website->description ?: 'No description'],
            ['Status', $website->is_active ? '✅ Active' : '❌ Inactive'],
            ['Created', $website->created_at->format('Y-m-d H:i:s')],
            ['Updated', $website->updated_at->format('Y-m-d H:i:s')],
            ['Last Scraped', $website->last_scraped_at ? $website->last_scraped_at->format('Y-m-d H:i:s') : 'Never'],
            ['Content Length', $website->content ? number_format(strlen($website->content)) . ' characters' : 'No content'],
        ];

        $this->table(['Property', 'Value'], $details);

        if ($website->content) {
            $this->newLine();
            $this->info("📝 Content Preview (first 200 characters):");
            $this->line(Str::limit($website->content, 200));
        }

        if ($website->metadata) {
            $this->newLine();
            $this->info("🔧 Metadata:");
            $this->line(json_encode($website->metadata, JSON_PRETTY_PRINT));
        }

        return Command::SUCCESS;
    }
}
