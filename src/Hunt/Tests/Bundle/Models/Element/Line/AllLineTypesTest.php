<?php

namespace Hunt\Tests\Bundle\Models\Element\Line;

use Hunt\Bundle\Exceptions\LineFactoryException;
use Hunt\Bundle\Exceptions\LineProcessFlowChangeException;
use Hunt\Bundle\Models\Element\Line\Line;
use Hunt\Bundle\Models\Element\Line\LineFactory;
use Hunt\Bundle\Models\Element\Line\Parts\PartsCollection;
use Hunt\Tests\HuntTestCase;

class AllLineTypesTest extends HuntTestCase
{
    /**
     * @dataProvider dataProviderForTestParsedLine
     * @param string $lineContent
     * @param array $toExclude
     * @param PartsCollection $parts
     * @param PartsCollection $expected
     *
     * @throws LineProcessFlowChangeException
     * @throws LineFactoryException
     */
    public function testAddExcludedTermsBack(
        string $lineContent,
        array $toExclude,
        PartsCollection $parts,
        PartsCollection $expected
    ) {
        //Start with a Line
        $line = new Line($lineContent);
        $line->setLineNumber(1);
        $line->removeExcludedTerms($toExclude);

        $parsedLine = LineFactory::getParsed($line, $parts);
        $this->assertEquals($parts, $parsedLine->getParts());

        $parsedLine->addExcludedTermsBack();

        $this->assertEquals($expected, $parsedLine->getParts());
    }

    public function dataProviderForTestParsedLine(): array
    {
        $ph = Line::getPlaceholder();

        return [
            'exclude "this", match "is"' => [
                'content' => 'this is a test of a line we want to exclude',
                'exclude' => [
                    'this',
                ],
                'parts_before' => $this->getLinePartsForTest([
                    'n1' => $ph . ' ',
                    'm1' => 'is',
                    'n2' => ' a test of a line we want to exclude',
                ]),
                'parts_after' => $this->getLinePartsForTest([
                    'e1' => 'this',
                    'n1' => ' ',
                    'm1' => 'is',
                    'n2' => ' a test of a line we want to exclude',
                ]),
            ],
            'exclude "this", match "is", multiple matches' => [
                'content' => 'this is a test of this line we want to exclude',
                'exclude' => [
                    'this',
                ],
                'parts_before' => $this->getLinePartsForTest([
                    'n1' => $ph . ' ',
                    'm1' => 'is',
                    'n2' => ' a test of ' . $ph . ' line we want to exclude',
                ]),
                'parts_after' => $this->getLinePartsForTest([
                    'e1' => 'this',
                    'n1' => ' ',
                    'm1' => 'is',
                    'n2' => ' a test of ',
                    'e2' => 'this',
                    'n3' => ' line we want to exclude',
                ]),
            ],
            'exclude "this" and "that", match "is", multiple matches' => [
                'content' => 'this is a test that we want to exclude',
                'exclude' => [
                    'this',
                    'that',
                ],
                'parts_before' => $this->getLinePartsForTest([
                    'n1' => $ph . ' ',
                    'm1' => 'is',
                    'n2' => ' a test ' . $ph . $ph . ' we want to exclude',
                ]),
                'parts_after' => $this->getLinePartsForTest([
                    'e1' => 'this',
                    'n1' => ' ',
                    'm1' => 'is',
                    'n2' => ' a test ',
                    'e2' => 'that',
                    'n3' => ' we want to exclude',
                ]),
            ],
            'nothing excluded' => [
                'content' => 'this is a test',
                'exclude' => [],
                'parts_before' => $this->getLinePartsForTest([
                    'n1' => 'th',
                    'm1' => 'is',
                    'n2' => ' ',
                    'm2' => 'is',
                    'n3' => ' a test',
                ]),
                'parts_after' => $this->getLinePartsForTest([
                    'n1' => 'th',
                    'm1' => 'is',
                    'n2' => ' ',
                    'm2' => 'is',
                    'n3' => ' a test',
                ]),
            ],
        ];
    }
}
