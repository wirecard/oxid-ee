<?php

/**
 * Ensures that all imports are being used.
 *
 * @since 1.0.1
 */
class Custom_Sniffs_CodeAnalysis_UnusedImportSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @inheritdoc
     *
     * @since 1.0.1
     */
    public function register()
    {
        return [T_USE];
    }

    /**
     * @inheritdoc
     *
     * @since 1.0.1
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $tokenColonPosition = $phpcsFile->findNext(T_SEMICOLON, $stackPtr);
        $tokenImportPosition = $tokenColonPosition ? $tokenColonPosition - 1 : null;
        $tokenImport = $tokenImportPosition ? $tokens[$tokenImportPosition] : null;
        $isUsed = false;

        foreach ($tokens as $token) {
            if ($token === $tokenImport || empty($token['content'])) {
                continue;
            }

            if ($token['content'] === $tokenImport['content'] || (
                $token['code'] === T_DOC_COMMENT_STRING && strpos($token['content'], $tokenImport['content']) !== false
            )) {
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
                for ($i = $stackPtr; $i < $tokenColonPosition + 2; $i++) {
                    $phpcsFile->fixer->replaceToken($i, '');
                }
            }
        }
    }
}
