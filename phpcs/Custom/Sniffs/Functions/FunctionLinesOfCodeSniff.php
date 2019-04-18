<?php

/**
 * Ensures that functions do not exceed a certain amount of lines of code.
 *
 * @see https://docs.oxid-esales.com/developer/en/6.0/modules/certification/software_quality.html#maximum-length-of-methods-80-lines
 */
class Custom_Sniffs_Functions_FunctionLinesOfCodeSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * The limit of lines of code a function should not exceed.
     *
     * @var int
     */
    public $linesLimit = 80;

    /**
     * The limit of lines of code a function must not exceed.
     *
     * @var int
     */
    public $absoluteLinesLimit = 120;

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_FUNCTION];
    }

    /**
     * @inheritdoc
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $tokenFunction = $tokens[$stackPtr];

        // ignore abstract functions
        if (!isset($tokenFunction['scope_opener']) || !isset($tokenFunction['scope_closer'])) {
            return;
        }

        $tokenScopeOpener = $tokens[$tokenFunction['scope_opener']];
        $tokenScopeCloser = $tokens[$tokenFunction['scope_closer']];
        $linesOfCode = $tokenScopeCloser['line'] - $tokenScopeOpener['line'] - 1;

        if ($linesOfCode > $this->absoluteLinesLimit) {
            $phpcsFile->addError(
                'Function exceeds maximum limit of %s lines of code; contains %s lines',
                $stackPtr,
                'MaxExceeded',
                [
                    $this->absoluteLinesLimit,
                    $linesOfCode,
                ]
            );
        } else if ($linesOfCode > $this->linesLimit) {
            $phpcsFile->addWarning(
                'Function exceeds %s lines of code; contains %s lines',
                $stackPtr,
                'TooLong',
                [
                    $this->linesLimit,
                    $linesOfCode,
                ]
            );
        }
    }
}
