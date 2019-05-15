<?php

/**
 * Ensures that the last entry in a multi-line array is followed by a comma.
 *
 * @since 1.0.1
 */
class Custom_Sniffs_Arrays_MultiLineArrayCommaSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @inheritdoc
     *
     * @since 1.0.1
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
     *
     * @since 1.0.1
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $tokenArray = $tokens[$stackPtr];
        $tokenOpenerPosition = $tokenArray['parenthesis_opener'] ?? $tokenArray['bracket_opener'];
        $tokenCloserPosition = $tokenArray['parenthesis_closer'] ?? $tokenArray['bracket_closer'];
        $tokenOpener = $tokens[$tokenOpenerPosition];
        $tokenCloser = $tokens[$tokenCloserPosition];

        // bail if this is not a multi-line array
        if ($tokenOpener['line'] === $tokenCloser['line']) {
            return;
        }

        $tokenLastPosition = $phpcsFile->findPrevious([T_WHITESPACE, T_COMMENT], $tokenCloserPosition - 1, null, true);
        $tokenLast = $tokens[$tokenLastPosition];

        if ($tokenLast['code'] !== T_COMMA && $tokenLast !== $tokenOpener) {
            $fix = $phpcsFile->addFixableError(
                'The last entry of a multi-line array must be followed by a comma',
                $tokenLastPosition,
                'LastEntryComma'
            );

            if ($fix) {
                $phpcsFile->fixer->addContent($tokenLastPosition, ',');
            }
        }
    }
}
