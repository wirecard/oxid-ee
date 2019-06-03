<?php

/**
 * Ensures that imports within the same namespace are not used.
 *
 * @since 1.2.0
 */
class Custom_Sniffs_CodeAnalysis_UnnecessaryImportSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @inheritdoc
     *
     * @since 1.2.0
     */
    public function register()
    {
        return [T_USE];
    }

    /**
     * @inheritdoc
     *
     * @since 1.2.0
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $tokenNamespacePosition = $phpcsFile->findNext(T_NAMESPACE, 0);
        $tokenColonPosition = $phpcsFile->findNext(T_SEMICOLON, $stackPtr);
        $tokenImportPosition = $tokenColonPosition ? $tokenColonPosition - 1 : null;
        $baseNamespace = '';
        $importNamespace = '';

        if ($tokenNamespacePosition) {
            for ($i = $tokenNamespacePosition + 2; $i < $phpcsFile->findNext(T_SEMICOLON, $tokenNamespacePosition); $i++) {
                $baseNamespace .= $tokens[$i]['content'];
            }
        }

        if ($tokenImportPosition) {
            for ($i = $stackPtr + 2; $i < $tokenImportPosition - 1; $i++) {
                $importNamespace .= $tokens[$i]['content'];
            }
        }

        if ($importNamespace === $baseNamespace) {
            $fix = $phpcsFile->addFixableError(
                '%s does not need to be imported ' . ($baseNamespace ? '(already in the namespace %s)' : '(no namespace declared)'),
                $stackPtr,
                'UnnecessaryImport',
                [
                    $tokens[$tokenImportPosition]['content'],
                    $baseNamespace,
                ]
            );

            if ($fix) {
                for ($i = $stackPtr; $i < $tokenColonPosition + 2; $i++) {
                    $phpcsFile->fixer->replaceToken($i, '');
                }
            }
        }
    }
}
