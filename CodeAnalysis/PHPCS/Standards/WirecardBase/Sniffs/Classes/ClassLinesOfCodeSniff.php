<?php

/**
 * Ensures that classes do not exceed a certain amount of lines of code.
 */
class WirecardBase_Sniffs_Classes_ClassLinesOfCodeSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * The limit of lines of code a class should not exceed.
     *
     * @var int
     */
    public $limit = 400;

    /**
     * The limit of lines of code a class must not exceed.
     *
     * @var int
     */
    public $absoluteLimit = 500;

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_CLASS,
            T_INTERFACE,
            T_TRAIT,
        ];
    }

    /**
     * @inheritdoc
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $linesOfCode = WirecardBase_Helpers_TokenHelper::getLinesOfCode($phpcsFile->getTokens());

        if ($linesOfCode > $this->absoluteLimit) {
            $phpcsFile->addError(
                'Class exceeds maximum limit of %s lines of code; contains %s lines',
                $stackPtr,
                'MaxExceeded',
                [
                    $this->absoluteLimit,
                    $linesOfCode,
                ]
            );

            return;
        }

        if ($linesOfCode > $this->limit) {
            $phpcsFile->addWarning(
                'Class exceeds %s lines of code; contains %s lines',
                $stackPtr,
                'TooLong',
                [
                    $this->limit,
                    $linesOfCode,
                ]
            );
        }
    }
}
