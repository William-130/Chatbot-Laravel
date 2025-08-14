<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RagWebsite;
use Illuminate\Support\Facades\Http;
use DOMDocument;
use DOMXPath;

class ScrapeWebsiteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:website
                           {url? : The URL to scrape}
                           {--name= : Website name}
                           {--description= : Website description}
                           {--update : Update existing website if found}
                           {--all : Re-scrape all existing websites}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape website content for RAG chatbot';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            return $this->scrapeAllWebsites();
        }

        $url = $this->argument('url');
        
        if (!$url) {
            $this->error('URL is required when not using --all option');
            return Command::FAILURE;
        }

        $name = $this->option('name') ?: $this->ask('Website name');
        $description = $this->option('description') ?: $this->ask('Website description (optional)', '');

        $this->info("Starting to scrape: {$url}");

        try {
            // Check if website exists
            $existingWebsite = RagWebsite::where('url', $url)->first();
            
            if ($existingWebsite && !$this->option('update')) {
                if (!$this->confirm("Website '{$existingWebsite->name}' already exists. Update it?")) {
                    $this->warn('Scraping cancelled.');
                    return Command::FAILURE;
                }
            }

            // Scrape content
            $this->line('Fetching website content...');
            $content = $this->scrapeWebsiteContent($url);
            
            if (empty($content)) {
                $this->error('No content could be extracted from the website.');
                return Command::FAILURE;
            }

            $this->info("Content extracted: " . strlen($content) . " characters");

            // Save to database
            if ($existingWebsite) {
                $existingWebsite->update([
                    'name' => $name,
                    'description' => $description,
                    'content' => $content,
                    'last_scraped_at' => now(),
                    'is_active' => true,
                    'metadata' => [
                        'scraped_by' => 'command',
                        'content_length' => strlen($content),
                        'updated_at' => now()->toISOString()
                    ]
                ]);
                $this->info("✅ Website '{$name}' updated successfully!");
            } else {
                $website = RagWebsite::create([
                    'name' => $name,
                    'url' => $url,
                    'description' => $description,
                    'content' => $content,
                    'is_active' => true,
                    'last_scraped_at' => now(),
                    'metadata' => [
                        'scraped_by' => 'command',
                        'content_length' => strlen($content),
                        'created_at' => now()->toISOString()
                    ]
                ]);
                $this->info("✅ Website '{$name}' added successfully!");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Failed to scrape website: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    private function scrapeAllWebsites()
    {
        $websites = RagWebsite::where('is_active', true)->get();
        
        if ($websites->isEmpty()) {
            $this->warn('No websites found to scrape.');
            return Command::SUCCESS;
        }

        $this->info("Found {$websites->count()} websites to re-scrape.");
        
        $progressBar = $this->output->createProgressBar($websites->count());
        $progressBar->start();

        $successful = 0;
        $failed = 0;

        foreach ($websites as $website) {
            try {
                $content = $this->scrapeWebsiteContent($website->url);
                
                $website->update([
                    'content' => $content,
                    'last_scraped_at' => now(),
                    'metadata' => array_merge($website->metadata ?? [], [
                        'last_scrape_by' => 'command_all',
                        'content_length' => strlen($content),
                        'updated_at' => now()->toISOString()
                    ])
                ]);

                $successful++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed to scrape {$website->name}: {$e->getMessage()}");
                $failed++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("Scraping completed!");
        $this->line("✅ Successful: {$successful}");
        if ($failed > 0) {
            $this->line("❌ Failed: {$failed}");
        }

        return Command::SUCCESS;
    }

    private function scrapeWebsiteContent($url)
    {
        // Make HTTP request with user agent
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ])->withOptions([
            'verify' => false // Disable SSL verification for development
        ])->timeout(30)->get($url);

        if (!$response->successful()) {
            throw new \Exception("HTTP {$response->status()}: Failed to fetch website content");
        }

        $html = $response->body();
        
        // Parse HTML and extract text content
        $content = $this->extractTextFromHtml($html);
        
        // Clean and limit content
        $content = $this->cleanContent($content);
        
        return $content;
    }

    private function extractTextFromHtml($html)
    {
        $doc = new DOMDocument();
        
        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);
        
        // Remove script and style elements
        $scripts = $xpath->query('//script | //style | //nav | //header | //footer | //aside');
        foreach ($scripts as $script) {
            $script->parentNode->removeChild($script);
        }

        // Extract text from important elements
        $textElements = $xpath->query('//h1 | //h2 | //h3 | //h4 | //h5 | //h6 | //p | //div[@class="content"] | //article | //section | //main');
        
        $content = '';
        foreach ($textElements as $element) {
            $text = trim($element->textContent);
            if (strlen($text) > 20) { // Only include meaningful text
                $content .= $text . "\n\n";
            }
        }

        return $content;
    }

    private function cleanContent($content)
    {
        // Remove extra whitespace and normalize
        $content = preg_replace('/\s+/', ' ', $content);
        $content = preg_replace('/\n\s*\n/', "\n\n", $content);
        
        // Remove unwanted characters
        $content = preg_replace('/[^\w\s\.\,\!\?\:\;\-\(\)\[\]\{\}\"\']/u', '', $content);
        
        // Limit content length (for token limits)
        if (strlen($content) > 10000) {
            $content = substr($content, 0, 10000) . '...';
        }
        
        return trim($content);
    }
}
