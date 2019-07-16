<?php

/**
 * Ensures that variable names are no longer than a specified limit.
 */
class WirecardBase_Sniffs_NamingConventions_VariableNameLengthSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * The limit of characters a variable name should not exceed.
     *
     * @var int
     */
    public $limit = 20;

    /**
     * The limit of characters a variable name must not exceed.
     *
     * @var int
     */
    public $absoluteLimit = 30;

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_VARIABLE];
    }

    /**
     * @inheritdoc
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $tokenVariable = $tokens[$stackPtr];
        $variableNameLength = strlen($tokenVariable['content']) - 1;

        if ($variableNameLength > $this->absoluteLimit) {
            $phpcsFile->addError(
                'Variable %s exceeds maximum limit of %s characters; contains %s characters',
                $stackPtr,
                'MaxExceeded',
                [
                    $tokenVariable['content'],
                    $this->absoluteLimit,
                    $variableNameLength,
                ]
            );

            return;
        }

        if ($variableNameLength > $this->limit) {
            $phpcsFile->addWarning(
                'Variable %s exceeds %s characters; contains %s characters',
                $stackPtr,
                'TooLong',
                [
                    $tokenVariable['content'],
                    $this->limit,
                    $variableNameLength,
                ]
            );
        }
    }
}
