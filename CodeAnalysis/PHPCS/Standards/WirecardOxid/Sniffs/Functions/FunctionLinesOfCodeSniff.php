<?php

/**
 * Ensures that functions do not exceed a certain amount of lines of code.
 */
class WirecardOxid_Sniffs_Functions_FunctionLinesOfCodeSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * The limit of lines of code a function should not exceed.
     *
     * @var int
     */
    public $limit = 80;

    /**
     * The limit of lines of code a function must not exceed.
     *
     * @var int
     */
    public $absoluteLimit = 120;

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
        $tokenBlock = $tokens[$stackPtr];

        if (empty($tokenBlock['scope_opener']) || empty($tokenBlock['scope_closer'])) {
            return;
        }

        $linesOfCode = WirecardBase_Helpers_TokenHelper::getLinesOfCode(
            $tokens,
            $tokenBlock['scope_opener'] + 1,
            $tokenBlock['scope_closer'] - 1
        );

        if ($linesOfCode > $this->absoluteLimit) {
            $phpcsFile->addError(
                'Function exceeds maximum limit of %s lines of code; contains %s lines',
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
                'Function exceeds %s lines of code; contains %s lines',
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
