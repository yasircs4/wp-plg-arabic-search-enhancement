<?php
/**
 * Test Arabic Text Normalizer
 *
 * @copyright 2024 Yasir Najeep
 * @license   GPL v2 or later
 */

namespace ArabicSearchEnhancement\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ArabicSearchEnhancement\Core\ArabicTextNormalizer;

class ArabicTextNormalizerTest extends TestCase {
    
    private ArabicTextNormalizer $normalizer;
    
    protected function setUp(): void {
        $mockCache = $this->createMock('ArabicSearchEnhancement\Interfaces\CacheInterface');
        $this->normalizer = new ArabicTextNormalizer($mockCache);
    }
    
    public function testRemovesDiacritics(): void {
        $input = 'مَكْتُوب';
        $expected = 'مكتوب';
        $result = $this->normalizer->normalize($input);
        
        $this->assertEquals($expected, $result);
    }
    
    public function testNormalizesAlefVariations(): void {
        $inputs = ['أكتب', 'إكتب', 'آكتب', 'ٱكتب'];
        $expected = 'اكتب';
        
        foreach ($inputs as $input) {
            $result = $this->normalizer->normalize($input);
            $this->assertEquals($expected, $result, "Failed for input: $input");
        }
    }
    
    public function testNormalizesTaaMarbuta(): void {
        $input = 'مدرسة';
        $expected = 'مدرسه';
        $result = $this->normalizer->normalize($input);
        
        $this->assertEquals($expected, $result);
    }
    
    public function testNormalizesYaaVariations(): void {
        $input = 'على';
        $expected = 'علي';
        $result = $this->normalizer->normalize($input);
        
        $this->assertEquals($expected, $result);
    }
    
    public function testNormalizesHamzaVariations(): void {
        $testCases = [
            'مؤسسة' => 'موسسه',
            'سائل' => 'سايل'
        ];
        
        foreach ($testCases as $input => $expected) {
            $result = $this->normalizer->normalize($input);
            $this->assertEquals($expected, $result, "Failed for input: $input");
        }
    }
    
    public function testRemovesTatweel(): void {
        $input = 'الـكـتـاب';
        $expected = 'الكتاب';
        $result = $this->normalizer->normalize($input);
        
        $this->assertEquals($expected, $result);
    }
    
    public function testComplexNormalization(): void {
        $input = 'مَكْتَبَةُ الْجَامِعَةِ الأَمْرِيكِيَّةِ';
        $expected = 'مكتبه الجامعه الامريكيه';
        $result = $this->normalizer->normalize($input);
        
        $this->assertEquals($expected, $result);
    }
    
    public function testEmptyStringHandling(): void {
        $result = $this->normalizer->normalize('');
        $this->assertEquals('', $result);
    }
    
    public function testNonArabicTextPassthrough(): void {
        $input = 'Hello World 123';
        $result = $this->normalizer->normalize($input);
        
        $this->assertEquals($input, $result);
    }
    
    public function testMixedArabicEnglishText(): void {
        $input = 'كِتَاب PHP Programming';
        $expected = 'كتاب PHP Programming';
        $result = $this->normalizer->normalize($input);
        
        $this->assertEquals($expected, $result);
    }
    
    public function testPreservesNumbers(): void {
        $input = 'الفصل ١٢٣';
        $expected = 'الفصل ١٢٣';
        $result = $this->normalizer->normalize($input);
        
        $this->assertEquals($expected, $result);
    }
}