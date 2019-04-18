<?php

/**
 * Ensures that classes are named in PascalCase.
 */
class WdStandard_Sniffs_NamingConventions_PascalCaseClassNameSniff implements PHP_CodeSniffer_Sniff
{
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
        $tokenClassName = $tokens[$phpcsFile->findNext(T_STRING, $stackPtr)];

        if (!preg_match('/^[A-Z][a-z]+(?:[A-Z][a-z]+)*$/', $tokenClassName['content'])) {
            $phpcsFile->addError("Class name {$tokenClassName['content']} is not PascalCase", $stackPtr, 'NotPascalCase');
        }
    }
}
