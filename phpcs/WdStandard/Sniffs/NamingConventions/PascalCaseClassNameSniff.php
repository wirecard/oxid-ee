<?php

/**
 * Tests that classes are named in PascalCase.
 */
class WdStandard_Sniffs_NamingConventions_PascalCaseClassNameSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_CLASS, T_INTERFACE];
    }

    /**
     * @inheritdoc
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $tokenPosition = $phpcsFile->findNext(T_STRING, $stackPtr);
        $token = $tokens[$tokenPosition];

        if (!preg_match('/^[A-Z][a-z]+(?:[A-Z][a-z]+)*$/', $token['content'])) {
            $phpcsFile->addError("Class name {$token['content']} is not PascalCase", $stackPtr, 'NotPascalCase');
        }
    }
}
