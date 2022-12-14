<?php

namespace Hunt\Bundle\Models\Element\Formatter;

use Hunt\Bundle\Exceptions\FormatterException;
use Hunt\Bundle\Exceptions\InvalidElementException;
use Hunt\Bundle\Models\Element\ElementInterface;
use Hunt\Bundle\Models\Element\Line\LineInterface;
use Hunt\Bundle\Models\Element\Line\ParsedLine;

class Formatter implements FormatterInterface
{
    const BEFORE = 'b';
    const AFTER = 'a';

    protected $wrapMap = [];

    /**
     * Get the line formatted.
     *
     * @param LineInterface $line The line to format.
     *
     * @return string The final formatted line string.
     *
     * @throws InvalidElementException
     */
    public function getFormattedLine(LineInterface $line): string
    {
        $finalContent = '';

        //Add our line number element
        $finalContent .= $this->format($line->getLineNumberElement());

        if ($line instanceof ParsedLine) {
            foreach ($line->getParts() as $element) {
                $finalContent .= $this->format($element);
            }
        } else {
            $finalContent .= $this->format($line);
        }

        return $finalContent;
    }

    /**
     * Format the given element.
     *
     * @param ElementInterface $element
     *
     * @return string The formatted element string.
     *
     * @throws InvalidElementException If the given element does not implement ElementInterface
     */
    public function format(ElementInterface $element): string
    {
        return $this->getBeforeContentForElement($element)
            . $element->getContent()
            . $this->getAfterContentForElement($element);
    }

    /**
     * @param string|ElementInterface $element       The FQCN for an instance which implements the ElementInterface.
     * @param string                  $beforeContent content to put before the given element type when formatted.
     * @param string                  $afterContent  content to put after the given element type when formatted.
     *
     * @return Formatter
     *
     * @throws FormatterException
     */
    public function setSidesForElement($element, string $beforeContent, string $afterContent): FormatterInterface
    {
        try {
            $this->setBeforeContentForElement($element, $beforeContent);
            $this->setAfterContentForElement($element, $afterContent);
        } catch (InvalidElementException $e) {
            throw new FormatterException('Cannot set sides for element.', null, $e);
        }

        return $this;
    }

    /**
     * Set the content to be used before the given element when formatted.
     *
     * @param string|ElementInterface $element       The FQCN, or an object, for an instance which implements the
     *                                               ElementInterface.
     * @param string                  $beforeContent The string to place before the element when formatted.
     *
     * @throws InvalidElementException
     */
    public function setBeforeContentForElement($element, string $beforeContent): FormatterInterface
    {
        $this->addElementSideContent(
            $this->getElementClass($element),
            self::BEFORE,
            $beforeContent
        );

        return $this;
    }

    /**
     * Set the content to be used after the given element when formatted.
     *
     * @param string $element The FQCN for an instance which implements the ElementInterface.
     * @param string $afterContent The content to place after the element when formatted.
     * @return FormatterInterface
     *
     * @throws InvalidElementException
     */
    public function setAfterContentForElement($element, string $afterContent): FormatterInterface
    {
        $this->addElementSideContent(
            $this->getElementClass($element),
            self::AFTER,
            $afterContent
        );

        return $this;
    }

    /**
     * @param ElementInterface|string $element An instance of ElementInterface or the FQCN for an element type.
     *
     * @throws InvalidElementException If the given element does not implement ElementInterface.
     */
    public function getBeforeContentForElement($element): string
    {
        return $this->getElementSideContent($this->getElementClass($element), self::BEFORE);
    }

    /**
     * @param ElementInterface|string $element An instance of ElementInterface or the FQCN for a part type.
     *
     * @throws InvalidElementException If the given element does not implement ElementInterface.
     */
    public function getAfterContentForElement($element): string
    {
        return $this->getElementSideContent($this->getElementClass($element), self::AFTER);
    }

    /**
     * @param string $element the FQCN for the part type class.
     * @param string $side One of BEFORE or AFTER constants.
     * @param string $content The content to be placed on the given side.
     */
    private function addElementSideContent(string $element, string $side, string $content): Formatter
    {
        $this->wrapMap[$element][$side] = $content;

        return $this;
    }

    /**
     * Return the content for the given part side.
     *
     * @param string $element the FQCN for the part type class.
     * @param string $side One of the BEFORE or AFTER constants.
     *
     * @return string The content for the given side.
     */
    private function getElementSideContent(string $element, string $side): string
    {
        $sides = array_key_exists($element, $this->wrapMap) ? $this->wrapMap[$element] : [];

        return (array_key_exists($side, $sides)) ? $sides[$side] : '';
    }

    /**
     * @param $element string|ElementInterface An element to get the FQCN name for.
     *
     * @throws InvalidElementException
     */
    private function getElementClass($element): string
    {
        if ($element instanceof ElementInterface) {
            return get_class($element);
        }

        if (in_array(ElementInterface::class, class_implements($element), true)) {
            return $element;
        }

        throw new InvalidElementException($element);
    }
}
