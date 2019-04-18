<?php

/**
 * Ensures that use statements are not preceded by a slash.
 */
class Custom_Sniffs_CodeAnalysis_LeadingSlashUseStatementSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_USE];
    }

    /**
     * @inheritdoc
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $tokenSeparatorPosition = $stackPtr + 2;
        $tokenSeparator = $tokens[$tokenSeparatorPosition] ?? null;

        if ($tokenSeparator && $tokenSeparator['code'] === T_NS_SEPARATOR) {
            $fix = $phpcsFile->addFixableWarning(
                'Leading slashes are not required in use statements',
                $stackPtr,
                'LeadingSlash'
            );

            if ($fix) {
                $phpcsFile->fixer->replaceToken($tokenSeparatorPosition, '');
            }
        }
    }
}
