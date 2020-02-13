<?php

namespace Hunt\Tests\Component;

use Hunt\Component\Trimmer;
use Hunt\Tests\HuntTestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Hunt\Component\Trimmer
 */
class TrimmerTest extends HuntTestCase
{

    /**
     * @covers ::emptyLine
     * @testWith ["\n", true]
     *           ["", true]
     *           ["\n ", false]
     *           ["\ntext", false]
     */
    public function testEmptyLine(string $line, bool $expectation)
    {
        $this->assertEquals($expectation, Trimmer::emptyLine($line));
    }

    /**
     * @covers ::getShortestLeadingSpaces
     * @covers ::trim
     * @covers ::emptyLine
     * @dataProvider dataProviderForTestTrim
     */
    public function testTrim($input, $output, int $expectedNumToTrim)
    {
        $numToTrim = Trimmer::getShortestLeadingSpaces($input);
        $this->assertEquals($expectedNumToTrim, $numToTrim);

        $this->assertEquals($output, Trimmer::trim($input, $numToTrim));
    }

    public function dataProviderForTestTrim(): array
    {
        return [
            'single line' => [
                'input' => '    this is line 1',
                'output' => 'this is line 1',
                'num_to_trim' => 4,
            ],
            'nothing to trim' => [
                'input' => [
                    'line 1',
                    'line 2',
                ],
                'output' => [
                    'line 1',
                    'line 2'
                ],
                'num_to_trim' => 0,
            ],
            'nothing to trim because first character is non-blank' => [
                'input' => [
                    'line 1',
                    '    line 2',
                ],
                'output' => [
                    'line 1',
                    '    line 2'
                ],
                'num_to_trim' => 0,
            ],
            'trim 1' => [
                'input' => [
                    ' line 1',
                    '    line 2',
                ],
                'output' => [
                    'line 1',
                    '   line 2'
                ],
                'num_to_trim' => 1,
            ],
            'trim 4' => [
                'input' => [
                    '    line 1',
                    '        line 2',
                ],
                'output' => [
                    'line 1',
                    '    line 2'
                ],
                'num_to_trim' => 4,
            ],
            'tabs are not handled' => [
                'input' => [
                    "\tline 1",
                    "\t        line 2",
                ],
                'output' => [
                    "\tline 1",
                    "\t        line 2"
                ],
                'num_to_trim' => 0,
            ],
        ];
    }
}
