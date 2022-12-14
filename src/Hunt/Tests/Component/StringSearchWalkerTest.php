<?php

namespace Hunt\Tests\Component;

use Hunt\Component\StringSearchWalker;
use PHPUnit\Framework\TestCase;

class StringSearchWalkerTest extends TestCase
{

    /**
     * @dataProvider dataProviderForTestWalker
     * @param string $content
     * @param string $term
     * @param array $expected
     * @param string $tail
     */
    public function testWalker(string $content, string $term, array $expected, string $tail)
    {
        $walker = new StringSearchWalker($content, $term);

        $i = 0;

        foreach($walker as $beforeContent) {
            $this->assertEquals($expected[$i], $beforeContent);
            ++$i;
        }

        $this->assertEquals($tail, $walker->tail());
    }

    public function dataProviderForTestWalker(): array
    {
        return [
            'simple' => [
                'content' => 'this is a WORD test where WORD will be replaced with a match',
                'term' => 'WORD',
                'expected' => [
                    'this is a ',
                    ' test where '
                ],
                'tail' => ' will be replaced with a match'
            ],
            'complex end' => [
                'content' => 'look at me, I am a WORD WORD WORD and a WORDWORDWORD',
                'term' => 'WORD',
                'expected' => [
                    'look at me, I am a ',
                    ' ',
                    ' ',
                    ' and a ',
                    '',
                    '',
                    ''
                ],
                'tail' => '',
            ],
            'complex start' => [
                'content' => 'WORDWORD WORD, look at me, I am a WORDMONKEYWORD',
                'term' => 'WORD',
                'expected' => [
                    '',
                    '',
                    ' ',
                    ', look at me, I am a ',
                    'MONKEY',
                ],
                'tail' => '',
            ],
        ];
    }
}
