<?php

namespace Hunt\Tests\Component;

use Hunt\Component\OutputStyler;
use Hunt\Tests\HuntTestCase;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * @codeCoverageIgnore
 */
class OutputStylerTest extends HuntTestCase
{


    ///**
    // * @dataProvider dataProviderForTestGetProgressBar
    // */
    //public function testGetProgressBar(bool $ansi, array $expectations)
    //{
    //    $cmd = new HuntCommand();
    //    $cmd->
    //    $input = $this->getInputObject(['--no-ansi' => !$ansi, 'command' => ]);
    //    $output = $this->getOutputMock();
    //    $progressBar = OutputStyler::getProgressBar($input, $output, $expectations['freq']);
    //    $this->assertInstanceOf(ProgressBar::class, $progressBar);
    //}

    public function dataProviderForTestGetProgressBar(): array
    {
        return [
            'ansi is true' => [
                'ansi' => true,
                'expectations' => [
                    'freq' => 100
                ]
            ],
            'ansi is false' => [
                'ansi' => false,
                'expectations' => [
                    'freq' => 500
                ]
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForTestApplyFormat
     * @covers \Hunt\Component\OutputStyler::applyFormat
     */
    public function testApplyFormat(string $styleName, array $expectations)
    {
        $text = 'styled text';
        $testLine = '<' . $styleName . '>' . $text . '</' . $styleName . '>';
        $formattedExpectation = "\033" . $expectations['startCode'] . $text . "\033" . $expectations['endCode'];
        $unformattedExpectation = $text;


        //Test when it's decorated
        $formatter = new OutputFormatter(true);
        OutputStyler::applyFormat($formatter);
        $line = $formatter->format($testLine);

        $this->assertInstanceOf($expectations['instanceof'], $formatter->getStyle($styleName));
        $this->assertEquals($formattedExpectation, $line);

        //Test when it's not decorated
        //Test when it's decorated
        $formatter = new OutputFormatter();
        OutputStyler::applyFormat($formatter);
        $line = $formatter->format($testLine);

        $this->assertInstanceOf($expectations['instanceof'], $formatter->getStyle($styleName));
        $this->assertEquals($unformattedExpectation, $line);
    }

    public function dataProviderForTestApplyFormat(): array
    {
        return [
            [
                'style' => 'info',
                'expectations' => [
                    'instanceof' => OutputFormatterStyle::class,
                    'startCode' => '[32m',
                    'endCode' => '[39m',
                ],
            ],
            [
                'style' => 'bold',
                'expectations' => [
                    'instanceof' => OutputFormatterStyle::class,
                    'startCode' => '[1m',
                    'endCode' => '[22m',
                ],
            ],
        ];
    }
}
