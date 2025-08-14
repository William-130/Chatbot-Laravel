<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RagWebsite extends Model
{
    protected $fillable = [
        'name',
        'url',
        'description',
        'content',
        'is_active',
        'last_scraped_at',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_scraped_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'website_id');
    }

    /**
     * Full-text search across name, description, and content
     */
    public static function fullTextSearch(string $query, float $minScore = 0.1)
    {
        $searchTerms = self::prepareSearchQuery($query);
        
        if (empty($searchTerms)) {
            return collect();
        }

        return self::select('*')
            ->selectRaw('MATCH(name, description, content) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance_score', [$searchTerms])
            ->whereRaw('MATCH(name, description, content) AGAINST(? IN NATURAL LANGUAGE MODE)', [$searchTerms])
            ->where('is_active', true)
            ->having('relevance_score', '>', $minScore)
            ->orderBy('relevance_score', 'desc')
            ->get();
    }

    /**
     * Boolean full-text search for more precise matching
     */
    public static function booleanSearch(string $query)
    {
        $searchTerms = self::prepareBooleanQuery($query);
        
        if (empty($searchTerms)) {
            return collect();
        }

        return self::select('*')
            ->selectRaw('MATCH(name, description, content) AGAINST(? IN BOOLEAN MODE) as relevance_score', [$searchTerms])
            ->whereRaw('MATCH(name, description, content) AGAINST(? IN BOOLEAN MODE)', [$searchTerms])
            ->where('is_active', true)
            ->orderBy('relevance_score', 'desc')
            ->get();
    }

    /**
     * Prepare search query for natural language mode
     */
    private static function prepareSearchQuery(string $query): string
    {
        // Remove special characters and normalize
        $query = preg_replace('/[^\w\s]/', ' ', $query);
        $query = preg_replace('/\s+/', ' ', trim($query));
        
        // Remove very short words (less than 3 characters)
        $words = explode(' ', $query);
        $words = array_filter($words, function($word) {
            return strlen($word) >= 3;
        });
        
        return implode(' ', $words);
    }

    /**
     * Prepare search query for boolean mode
     */
    private static function prepareBooleanQuery(string $query): string
    {
        $query = self::prepareSearchQuery($query);
        $words = explode(' ', $query);
        
        // Add + prefix for required words
        $booleanTerms = array_map(function($word) {
            return '+' . $word;
        }, $words);
        
        return implode(' ', $booleanTerms);
    }

    /**
     * Search with fallback methods
     */
    public static function smartSearch(string $query)
    {
        // Try full-text search first
        $results = self::fullTextSearch($query);
        
        if ($results->isNotEmpty()) {
            return $results;
        }
        
        // Fallback to boolean search
        $results = self::booleanSearch($query);
        
        if ($results->isNotEmpty()) {
            return $results;
        }
        
        // Final fallback to LIKE search
        return self::where('is_active', true)
            ->where(function($queryBuilder) use ($query) {
                $queryBuilder->where('name', 'like', "%{$query}%")
                           ->orWhere('description', 'like', "%{$query}%")
                           ->orWhere('content', 'like', "%{$query}%");
            })
            ->get();
    }
}
