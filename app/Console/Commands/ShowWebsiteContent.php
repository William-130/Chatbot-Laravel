<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RagWebsite;

class ShowWebsiteContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'show:website-content 
                           {--name= : Show content by website name}
                           {--url= : Show content by website URL}
                           {--all : Show all websites}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show content of scraped websites';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            return $this->showAllWebsites();
        }

        $name = $this->option('name');
        $url = $this->option('url');

        if (!$name && !$url) {
            $this->error('Please provide --name, --url, or --all option');
            return Command::FAILURE;
        }

        $website = null;
        
        if ($name) {
            $website = RagWebsite::where('name', 'like', "%{$name}%")->first();
        } elseif ($url) {
            $website = RagWebsite::where('url', 'like', "%{$url}%")->first();
        }

        if (!$website) {
            $this->error('Website not found');
            return Command::FAILURE;
        }

        $this->showWebsiteDetails($website);
        return Command::SUCCESS;
    }

    private function showAllWebsites()
    {
        $websites = RagWebsite::all();
        
        if ($websites->isEmpty()) {
            $this->warn('No websites found');
            return Command::SUCCESS;
        }

        $this->info("Found {$websites->count()} websites:");
        $this->newLine();

        foreach ($websites as $website) {
            $this->showWebsiteDetails($website);
            $this->newLine();
            $this->line(str_repeat('-', 80));
            $this->newLine();
        }

        return Command::SUCCESS;
    }

    private function showWebsiteDetails($website)
    {
        $this->info("🌐 Website: {$website->name}");
        $this->line("🔗 URL: {$website->url}");
        $this->line("📝 Description: {$website->description}");
        $this->line("📅 Last Scraped: {$website->last_scraped_at}");
        $this->line("✅ Active: " . ($website->is_active ? 'Yes' : 'No'));
        $this->line("📊 Content Length: " . strlen($website->content ?? '') . " characters");
        
        if ($website->metadata) {
            $this->line("🏷️ Metadata: " . json_encode($website->metadata, JSON_PRETTY_PRINT));
        }
        
        $this->newLine();
        $this->line("📄 Content Preview (first 500 chars):");
        $this->line(str_repeat('=', 50));
        
        $content = $website->content ?? 'No content available';
        $preview = strlen($content) > 500 ? substr($content, 0, 500) . '...' : $content;
        
        $this->line($preview);
    }
}
