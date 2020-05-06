<?php declare(strict_types=1);

namespace Jawira\CaseConverter\Split;

use Jawira\CaseConverter\CaseConverterException;

/**
 * Class Splitter
 *
 * A Splitter sub-class allows to read the words contained in a string
 *
 * @author  Jawira Portugal <dev@tugal.be>
 * @package Jawira\CaseConverter\Split
 */
abstract class Splitter
{
    /**
     * @var string Words extracted from input string
     */
    protected $inputString;

    public function __construct(string $inputString)
    {
        $this->inputString = $inputString;
    }

    /**
     * Tells how to split a string into valid words.
     *
     * @return string[]
     */
    abstract public function split(): array;

    /**
     * This is an utility method, typically this method is used by to split a string based on pattern.
     *
     * @param string $inputString
     * @param string $pattern
     *
     * @return string[]
     * @throws \Jawira\CaseConverter\CaseConverterException
     */
    protected function splitUsingPattern(string $inputString, string $pattern): array
    {
        $words = preg_split($pattern, $inputString, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        if ($words === false) {
            throw new CaseConverterException("Error while processing '{$this->inputString}'"); // @codeCoverageIgnore
        }

        return $words;
    }
}
