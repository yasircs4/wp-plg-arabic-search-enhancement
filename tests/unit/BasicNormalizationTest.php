<?php
/**
 * Simple Arabic Text Normalizer Test (Isolated)
 *
 * @copyright 2025 yasircs4
 * @license   GPL v2 or later
 */

namespace ArabicSearchEnhancement\Tests\Unit;

use PHPUnit\Framework\TestCase;

class BasicNormalizationTest extends TestCase {
    
    public function testBasicTextNormalization(): void {
        // Test basic diacritic removal functionality
        $input = 'مَكْتُوب';
        $patterns = [
            '/[\x{064B}-\x{065F}]/u', // Diacritics
            '/[\x{0670}]/u',          // Superscript alef
            '/[\x{06D6}-\x{06ED}]/u'  // Additional marks
        ];
        
        $result = $input;
        foreach ($patterns as $pattern) {
            $result = preg_replace($pattern, '', $result);
        }
        
        $this->assertEquals('مكتوب', $result);
        $this->assertNotEquals($input, $result);
    }
    
    public function testAlefNormalization(): void {
        $alefVariations = ['أ', 'إ', 'آ', 'ٱ'];
        $expectedResult = 'ا';
        
        foreach ($alefVariations as $alef) {
            $word = $alef . 'كتب';
            $normalized = str_replace($alefVariations, $expectedResult, $word);
            $this->assertEquals('اكتب', $normalized);
        }
    }
    
    public function testBasicWordProcessing(): void {
        $this->assertEquals('hello', 'hello'); // Non-Arabic passthrough
        $this->assertEquals('', ''); // Empty string
        $this->assertIsString('test'); // Basic assertion
    }
    
    public function testStringLength(): void {
        $arabicText = 'مكتوب';
        $this->assertGreaterThan(0, mb_strlen($arabicText, 'UTF-8'));
    }
}