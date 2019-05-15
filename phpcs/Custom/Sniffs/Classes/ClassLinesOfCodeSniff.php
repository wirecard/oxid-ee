<?php

/**
 * Ensures that classes do not exceed a certain amount of lines of code.
 *
 * @since 1.0.1
 */
class Custom_Sniffs_Classes_ClassLinesOfCodeSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * The limit of lines of code a class should not exceed.
     *
     * @var int
     *
     * @since 1.0.1
     */
    public $linesLimit = 400;

    /**
     * The limit of lines of code a class must not exceed.
     *
     * @var int
     *
     * @since 1.0.1
     */
    public $absoluteLinesLimit = 500;

    /**
     * @inheritdoc
     *
     * @since 1.0.1
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
     *
     * @since 1.0.1
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $tokenClass = $tokens[$stackPtr];

        // ignore abstract classes
        if (!isset($tokenClass['scope_opener']) || !isset($tokenClass['scope_closer'])) {
            return;
        }

        $tokenScopeOpener = $tokens[$tokenClass['scope_opener']];
        $tokenScopeCloser = $tokens[$tokenClass['scope_closer']];
        $linesOfCode = $tokenScopeCloser['line'] - $tokenScopeOpener['line'] - 1;

        if ($linesOfCode > $this->absoluteLinesLimit) {
            $phpcsFile->addError(
                'Class exceeds maximum limit of %s lines of code; contains %s lines',
                $stackPtr,
                'MaxExceeded',
                [
                    $this->absoluteLinesLimit,
                    $linesOfCode,
                ]
            );
        } else if ($linesOfCode > $this->linesLimit) {
            $phpcsFile->addWarning(
                'Class exceeds %s lines of code; contains %s lines',
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
