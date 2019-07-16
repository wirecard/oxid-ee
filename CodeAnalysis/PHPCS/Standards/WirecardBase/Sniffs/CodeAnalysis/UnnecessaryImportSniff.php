<?php

/**
 * Ensures that imports within the same namespace are not used.
 */
class WirecardBase_Sniffs_CodeAnalysis_UnnecessaryImportSniff implements PHP_CodeSniffer_Sniff
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

        $tokenNamespacePtr = $phpcsFile->findNext(T_NAMESPACE, 0);
        $tokenColonPtr = $phpcsFile->findNext(T_SEMICOLON, $stackPtr);
        $tokenImportPtr = $tokenColonPtr ? $tokenColonPtr - 1 : null;
        $baseNamespace = '';
        $importNamespace = '';

        if ($tokenNamespacePtr) {
            $tokenNextPtr = $phpcsFile->findNext(T_WHITESPACE, $tokenNamespacePtr + 1, null, true);

            for ($i = $tokenNextPtr; $i < $phpcsFile->findNext(T_SEMICOLON, $tokenNamespacePtr); $i++) {
                $baseNamespace .= $tokens[$i]['content'];
            }
        }

        if ($tokenImportPtr) {
            $tokenNextPtr = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

            for ($i = $tokenNextPtr; $i < $tokenImportPtr - 1; $i++) {
                $importNamespace .= $tokens[$i]['content'];
            }
        }

        if ($importNamespace === $baseNamespace) {
            $fix = $phpcsFile->addFixableError(
                '%s does not need to be imported ' .
                    ($baseNamespace ? '(already in the namespace %s)' : '(no namespace declared)'),
                $stackPtr,
                'UnnecessaryImport',
                [
                    $tokens[$tokenImportPtr]['content'],
                    $baseNamespace,
                ]
            );

            if ($fix) {
                for ($i = $stackPtr; $i < $tokenColonPtr + 2; $i++) {
                    $phpcsFile->fixer->replaceToken($i, '');
                }
            }
        }
    }
}
