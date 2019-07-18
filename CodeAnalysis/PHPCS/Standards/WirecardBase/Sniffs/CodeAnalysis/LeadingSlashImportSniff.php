<?php

/**
 * Ensures that imports are not preceded by a slash.
 */
class WirecardBase_Sniffs_CodeAnalysis_LeadingSlashImportSniff implements PHP_CodeSniffer_Sniff
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

        // only run this for root level imports
        if ($tokens[$stackPtr]['level']) {
            return;
        }

        $tokenNextPtr = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($tokenNextPtr && $tokens[$tokenNextPtr]['code'] === T_NS_SEPARATOR) {
            $fix = $phpcsFile->addFixableWarning(
                'Leading slashes are not required in use statements',
                $stackPtr,
                'LeadingSlash'
            );

            if ($fix) {
                $phpcsFile->fixer->replaceToken($tokenNextPtr, '');
            }
        }
    }
}
