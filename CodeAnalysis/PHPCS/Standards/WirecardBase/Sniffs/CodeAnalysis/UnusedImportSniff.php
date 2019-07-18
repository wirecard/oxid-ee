<?php

/**
 * Ensures that all imports are being used.
 */
class WirecardBase_Sniffs_CodeAnalysis_UnusedImportSniff implements PHP_CodeSniffer_Sniff
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
        $tokenColonPtr = $phpcsFile->findNext(T_SEMICOLON, $stackPtr);

        // only run this for root level imports
        if (!$tokenColonPtr || $tokens[$stackPtr]['level']) {
            return;
        }

        $tokenImport = $tokens[$tokenColonPtr - 1];
        $isUsed = false;

        for ($i = $tokenColonPtr; $i < count($tokens); $i++) {
            if (empty($tokens[$i]['content'])) {
                continue;
            }

            if (strpos($tokens[$i]['content'], $tokenImport['content']) !== false) {
                $isUsed = true;

                break;
            }
        }

        if (!$isUsed) {
            $fix = $phpcsFile->addFixableError(
                '%s is unused',
                $stackPtr,
                'UnusedImport',
                [$tokenImport['content']]
            );

            if ($fix) {
                $tokenNextPtr = $phpcsFile->findNext(T_WHITESPACE, $tokenColonPtr + 1, null, true);

                for ($i = $stackPtr; $i < $tokenNextPtr; $i++) {
                    $phpcsFile->fixer->replaceToken($i, '');
                }
            }
        }
    }
}
