<?php

/**
 * Ensures that classes do not exceed a certain amount of lines of code.
 */
class WdStandard_Sniffs_Classes_ClassLinesOfCodeSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * The limit of lines of code a class should not exceed.
     *
     * @var int
     */
    public $linesLimit = 400;

    /**
     * The limit of lines of code a class must not exceed.
     *
     * @var int
     */
    public $absoluteLinesLimit = 500;

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_CLASS,
            T_INTERFACE,
        ];
    }

    /**
     * @inheritdoc
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $tokenClass = $tokens[$stackPtr];
        $tokenScopeOpener = $tokens[$tokenClass['scope_opener']];
        $tokenScopeCloser = $tokens[$tokenClass['scope_closer']];
        $length = $tokenScopeCloser['line'] - $tokenScopeOpener['line'] - 1;

        if ($length > $this->absoluteLinesLimit) {
            $phpcsFile->addError(
                'Class exceeds maximum limit of %s lines of code; contains %s lines',
                $stackPtr,
                'MaxExceeded',
                [
                    $this->absoluteLinesLimit,
                    $length,
                ]
            );
        } else if ($length > $this->linesLimit) {
            $phpcsFile->addWarning(
                'Class exceeds %s lines of code; contains %s lines',
                $stackPtr,
                'TooLong',
                [
                    $this->linesLimit,
                    $length,
                ]
            );
        }
    }
}
