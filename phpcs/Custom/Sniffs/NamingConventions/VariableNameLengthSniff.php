<?php

/**
 * Ensures that variable names are no longer than a specified limit.
 */
class Custom_Sniffs_NamingConventions_VariableNameLengthSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * The limit of characters a variable name should not exceed.
     *
     * @var int
     */
    public $charactersLimit = 20;

    /**
     * The limit of characters a variable name must not exceed.
     *
     * @var int
     */
    public $absoluteCharactersLimit = 30;

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

        if ($variableNameLength > $this->absoluteCharactersLimit) {
            $phpcsFile->addError(
                'Variable name exceeds maximum limit of %s characters; contains %s characters',
                $stackPtr,
                'MaxExceeded',
                [
                    $this->absoluteCharactersLimit,
                    $variableNameLength,
                ]
            );
        } else if ($variableNameLength > $this->charactersLimit) {
            $phpcsFile->addWarning(
                'Variable name exceeds %s characters; contains %s characters',
                $stackPtr,
                'TooLong',
                [
                    $this->charactersLimit,
                    $variableNameLength,
                ]
            );
        }
    }
}
