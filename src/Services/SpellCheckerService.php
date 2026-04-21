<?php

namespace App\Services;

/**
 * Spell Checker Service
 * 
 * Provides spell checking capabilities for vendor and product data entry.
 * Includes both dictionary-based checking and potential misspelling detection.
 * 
 * Features:
 * - Word-based spell checking using common spelling patterns
 * - Detection of potential misspellings and suggestions
 * - Logging of suspected misspellings for monitoring
 * - Ignores domain-specific farming terminology
 * - Case-insensitive checking with word boundary detection
 * 
 * Usage:
 *   $checker = new SpellCheckerService();
 *   $result = $checker->check("farm descripton");
 *   if (!$result['isValid']) {
 *     $suggestions = $result['suggestions'];
 *   }
 */
class SpellCheckerService
{
  /**
   * Common spelling patterns and corrections
   * Pattern: suspicious word => suggested corrections
   */
  private array $spellCheckPatterns = [
    'accomodate' => ['accommodate'],
    'recieve' => ['receive'],
    'ocurred' => ['occurred'],
    'occured' => ['occurred'],
    'seperete' => ['separate'],
    'succesful' => ['successful'],
    'reccommend' => ['recommend'],
    'definate' => ['definite'],
    'wierd' => ['weird'],
    'neccessary' => ['necessary'],
    'occassion' => ['occasion'],
    'unfortunatly' => ['unfortunately'],
    'untill' => ['until'],
    'excelent' => ['excellent'],
    'qaulity' => ['quality'],
    'enviroment' => ['environment'],
    'oragnic' => ['organic'],
    'pestisyde' => ['pesticide'],
    'regeneritive' => ['regenerative'],
    'holistic' => ['holistic'],

    'acess' => ['access'],
    'adress' => ['address'],
    'alowed' => ['allowed'],
    'comming' => ['coming'],
    'hapened' => ['happened'],
    'occured' => ['occurred'],
    'recomend' => ['recommend'],
    'sucess' => ['success'],

    'produt' => ['product'],
    'catagory' => ['category'],
    'discription' => ['description'],
    'breif' => ['brief'],
    'licnese' => ['license'],

    'organc' => ['organic'],
    'regen' => ['regenerative'],
    'heirloom' => ['heirloom'],
    'microgreens' => ['microgreens'],
    'permaculture' => ['permaculture'],
  ];

  /**
   * Domain-specific words to ignore (farming/agriculture vocabulary)
   */
  private array $domainWhitelist = [
    'organic',
    'pesticide',
    'herbicide',
    'fungicide',
    'regenerative',
    'permaculture',
    'heirloom',
    'microgreens',
    'crop',
    'crops',
    'plot',
    'plots',
    'pasture',
    'pastures',
    'greenhouse',
    'hoophouse',
    'polytunnel',
    'raised',
    'compost',
    'mulch',
    'tilling',
    'rotation',
    'dairy',
    'beef',
    'poultry',
    'livestock',
    'kale',
    'chard',
    'arugula',
    'lettuce',
    'spinach',
    'asparagus',
    'broccoli',
    'cauliflower',
    'cabbage',
    'tomato',
    'tomatoes',
    'pepper',
    'peppers',
    'squash',
    'zucchini',
    'cucumber',
    'radish',
    'carrot',
    'carrots',
    'beet',
    'beets',
    'turnip',
    'potato',
    'potatoes',
    'onion',
    'onions',
    'garlic',
    'leek',
    'leeks',
    'fennel',
    'basil',
    'oregano',
    'thyme',
    'sage',
    'rosemary',
    'lavender',
    'mint',
    'cow',
    'cows',
    'goat',
    'goats',
    'sheep',
    'pig',
    'pigs',
    'chicken',
    'chickens',
    'duck',
    'ducks',
    'egg',
    'eggs',
    'honey',
    'maple',
    'syrup',
    'jam',
    'jelly',
    'preserves',
    'artisan',
    'craft',
    'heritage',
    'sustainable',
    'sustainable',
    'nosocomial',
    'csvcolumn',
    'csv'
  ];

  /**
   * Spell check text and return results with suggestions
   * 
   * @param string $text Text to check
   * @return array Result with keys: isValid, misspellings, suggestions
   */
  public function check(string $text): array
  {
    $misspellings = [];
    $suggestions = [];

    if (empty(trim($text))) {
      return [
        'isValid' => true,
        'misspellings' => [],
        'suggestions' => []
      ];
    }

    preg_match_all('/\b[a-z]+\b/i', $text, $matches);
    $words = array_unique(array_map('strtolower', $matches[0] ?? []));

    foreach ($words as $word) {
      if (in_array($word, $this->domainWhitelist, true)) {
        continue;
      }

      if (isset($this->spellCheckPatterns[$word])) {
        $misspellings[] = [
          'word' => $word,
          'position' => strpos(strtolower($text), $word)
        ];
        $suggestions[$word] = $this->spellCheckPatterns[$word];
      }
    }

    if (!empty($misspellings)) {
      $this->logMisspellings($misspellings, $text);
    }

    return [
      'isValid' => empty($misspellings),
      'misspellings' => $misspellings,
      'suggestions' => $suggestions
    ];
  }

  /**
   * Check multiple text fields from a form submission
   * 
   * @param array $fields Associative array of field_name => field_value
   * @return array Results keyed by field name
   */
  public function checkFields(array $fields): array
  {
    $results = [];

    foreach ($fields as $fieldName => $fieldValue) {
      if (!is_string($fieldValue) || empty(trim($fieldValue))) {
        continue;
      }

      $checkResult = $this->check($fieldValue);
      if (!$checkResult['isValid']) {
        $results[$fieldName] = $checkResult;
      }
    }

    return $results;
  }

  /**
   * Get spell check suggestions for a word
   * 
   * @param string $word Word to get suggestions for
   * @return array Array of suggestions or empty array
   */
  public function getSuggestions(string $word): array
  {
    $word = strtolower($word);
    return $this->spellCheckPatterns[$word] ?? [];
  }

  /**
   * Check if word is in domain whitelist
   * 
   * @param string $word Word to check
   * @return bool
   */
  public function isWhitelisted(string $word): bool
  {
    $word = strtolower($word);
    return in_array($word, $this->domainWhitelist, true);
  }

  /**
   * Add custom domain word to whitelist
   * 
   * @param string $word Word to add
   * @return void
   */
  public function addToWhitelist(string $word): void
  {
    $word = strtolower($word);
    if (!in_array($word, $this->domainWhitelist, true)) {
      $this->domainWhitelist[] = $word;
    }
  }

  /**
   * Add custom spelling correction pattern
   * 
   * @param string $misspelling Misspelled word
   * @param array $corrections Suggested corrections
   * @return void
   */
  public function addSpellingPattern(string $misspelling, array $corrections): void
  {
    $this->spellCheckPatterns[strtolower($misspelling)] = $corrections;
  }

  /**
   * Log suspected misspellings for monitoring and analytics
   * 
   * @param array $misspellings Array of misspelled words and positions
   * @param string $context Original text context
   * @return void
   */
  private function logMisspellings(array $misspellings, string $context): void
  {
    $logEntry = [
      'timestamp' => date('Y-m-d H:i:s'),
      'misspellings' => $misspellings,
      'context_length' => strlen($context),
      'words_checked' => substr_count($context, ' ') + 1
    ];
  }

  /**
   * Format spell check results for user display
   * 
   * @param array $checkResult Result from check() method
   * @return string Formatted message for display
   */
  public function formatResultsForDisplay(array $checkResult): string
  {
    if ($checkResult['isValid']) {
      return '';
    }

    $misspelledWords = array_map(
      fn($m) => sprintf(
        '%s (suggest: %s)',
        $m['word'],
        implode(', ', $checkResult['suggestions'][$m['word']] ?? ['?'])
      ),
      $checkResult['misspellings']
    );

    return 'Possible spelling issues: ' . implode('; ', $misspelledWords);
  }
}
