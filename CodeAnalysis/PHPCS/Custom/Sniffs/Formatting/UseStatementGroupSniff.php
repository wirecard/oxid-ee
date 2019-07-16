<?php

/**
 * Ensures that use statements are grouped.
 *
 * @since 1.1.0
 */
class Custom_Sniffs_Formatting_UseStatementGroupSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @inheritdoc
     *
     * @since 1.1.0
     */
    public function register()
    {
        return [T_USE];
    }

    /**
     * @inheritdoc
     *
     * @since 1.1.0
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $previousUseTokenPosition = $phpcsFile->findPrevious(T_USE, $stackPtr - 1);
        $nextUseTokenPosition = $phpcsFile->findNext(T_USE, $stackPtr + 1);

        if (!$previousUseTokenPosition || !$nextUseTokenPosition) {
            return;
        }

        $baseNameToken = $tokens[$phpcsFile->findNext(T_STRING, $stackPtr)];
        $previousBaseNameToken = $tokens[$phpcsFile->findNext(T_STRING, $previousUseTokenPosition)];
        $nextBaseNameToken = $tokens[$phpcsFile->findNext(T_STRING, $nextUseTokenPosition)];

        // if there are two or more use statements with the same base name, enforce a newline before the next one
        if ($previousBaseNameToken['content'] === $baseNameToken['content'] &&
            $nextBaseNameToken['content'] !== $baseNameToken['content'] &&
            $tokens[$nextUseTokenPosition - 2]['code'] !== T_WHITESPACE) {
            $fix = $phpcsFile->addFixableWarning('Use statements with equal base names should be grouped', $nextUseTokenPosition);

            if ($fix) {
                $phpcsFile->fixer->addContentBefore($nextUseTokenPosition, PHP_EOL);
            }
        }
    }
}
