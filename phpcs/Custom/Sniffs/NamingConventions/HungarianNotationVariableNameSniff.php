<?php

/**
 * Ensures that variable names are written in Hungarian notation (e.g. $sMyString, $iMyInt, $aMyArray, …)
 */
class Custom_Sniffs_NamingConventions_HungarianNotationVariableNameSniff implements PHP_CodeSniffer_Sniff
{
    const EXCLUDED_VARIABLE_NAMES = [
        '$this',
        '$_SERVER',
        '$_GET',
        '$_POST',
        '$_FILES',
        '$_COOKIE',
        '$_SESSION',
        '$_REQUEST',
        '$_ENV',
    ];

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
        $variableName = $tokenVariable['content'];

        if (in_array($variableName, self::EXCLUDED_VARIABLE_NAMES)) {
            return;
        }

        if (!$this->variableNameIsHungarianNotation($variableName)) {
            $phpcsFile->addError(
                'Variable name %s is not written in Hungarian notation',
                $stackPtr,
                'NotHungarianNotation',
                [$variableName]
            );
        }
    }

    /**
     * Checks if a given variable name is written in Hungarian notation.
     *
     * @param string $variableName
     * @return bool
     */
    private function variableNameIsHungarianNotation(string $variableName)
    {
        return preg_match('/^\$_?[a-z][A-Z]/', $variableName);
    }
}
