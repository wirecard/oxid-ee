<?php

/**
 * Ensures that all imports are being used.
 *
 * @since 1.1.0
 */
class Custom_Sniffs_CodeAnalysis_UnusedImportSniff implements PHP_CodeSniffer_Sniff
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
        $tokenColonPosition = $phpcsFile->findNext(T_SEMICOLON, $stackPtr);

        if (!$tokenColonPosition) {
            return;
        }

        $tokens = $phpcsFile->getTokens();
        $tokenImport = $tokens[$tokenColonPosition - 1];
        $isUsed = false;

        foreach ($tokens as $tokenPosition => $token) {
            if ($token === $tokenImport || empty($token['content'])) {
                continue;
            }

            if ($token['content'] === $tokenImport['content'] || $this->isDocBlockTagCommentAndContainsImport(
                $phpcsFile,
                $token,
                $tokenPosition,
                $tokenImport['content']
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

    private function isDocBlockTagCommentAndContainsImport($phpcsFile, $token, $tokenPosition, $importName)
    {
        return $token['code'] === T_DOC_COMMENT_STRING &&
            preg_match("/^[^\s]*{$importName}(\W|$)/", $token['content']) &&
            $phpcsFile->findFirstOnLine(T_DOC_COMMENT_TAG, $tokenPosition);
    }
}
