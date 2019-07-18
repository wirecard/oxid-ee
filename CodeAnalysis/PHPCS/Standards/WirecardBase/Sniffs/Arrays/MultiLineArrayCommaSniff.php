<?php

/**
 * Ensures that the last entry in a multi-line array is followed by a comma.
 */
class WirecardBase_Sniffs_Arrays_MultiLineArrayCommaSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_ARRAY,
            T_OPEN_SHORT_ARRAY,
        ];
    }

    /**
     * @inheritdoc
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $tokenArray = $tokens[$stackPtr];
        $tokenOpenerPtr = $tokenArray['parenthesis_opener'] ?? $tokenArray['bracket_opener'];
        $tokenCloserPtr = $tokenArray['parenthesis_closer'] ?? $tokenArray['bracket_closer'];
        $tokenOpener = $tokens[$tokenOpenerPtr];
        $tokenCloser = $tokens[$tokenCloserPtr];

        // bail if this is not a multi-line array
        if ($tokenOpener['line'] === $tokenCloser['line']) {
            return;
        }

        $tokenLastPtr = $phpcsFile->findPrevious([T_WHITESPACE, T_COMMENT], $tokenCloserPtr - 1, null, true);
        $tokenLast = $tokens[$tokenLastPtr];

        if ($tokenLast['code'] !== T_COMMA && $tokenLast !== $tokenOpener) {
            $fix = $phpcsFile->addFixableError(
                'The last entry of a multi-line array must be followed by a comma',
                $tokenLastPtr,
                'LastEntryComma'
            );

            if ($fix) {
                $phpcsFile->fixer->addContent($tokenLastPtr, ',');
            }
        }
    }
}
