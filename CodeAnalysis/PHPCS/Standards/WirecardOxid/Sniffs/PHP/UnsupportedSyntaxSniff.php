<?php

/**
 * Warns if PHP syntax not supported by OXID or the OXID testing framework is being used.
 */
class WirecardOxid_Sniffs_PHP_UnsupportedSyntaxSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_FUNCTION];
    }

    /**
     * @inheritdoc
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $tokenFunction = $tokens[$stackPtr];

        // check for type hints
        if (isset($tokenFunction['parenthesis_opener']) && isset($tokenFunction['parenthesis_closer'])) {
            for ($i = $tokenFunction['parenthesis_opener']; $i < $tokenFunction['parenthesis_closer']; $i++) {
                if (in_array($tokens[$i]['code'], [T_STRING, T_ARRAY_HINT])) {
                    $tokenNextPtr = $phpcsFile->findNext(T_WHITESPACE, $i + 1, null, true);

                    if (!$tokenNextPtr || $tokens[$tokenNextPtr]['code'] !== T_VARIABLE) {
                        continue;
                    }

                    $fix = $phpcsFile->addFixableWarning('Type hints should not be used', $i, 'TypeHint');

                    if ($fix) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }
                }
            }
        }

        // check for return types
        $tokenReturnTypePtr = isset($tokenFunction['scope_opener']) ? $phpcsFile->findNext(
            T_RETURN_TYPE,
            $tokenFunction['parenthesis_closer'],
            $tokenFunction['scope_opener']
        ) : null;

        if ($tokenReturnTypePtr) {
            $fix = $phpcsFile->addFixableWarning(
                'Return types should not be used',
                $tokenReturnTypePtr,
                'ReturnType'
            );

            if ($fix) {
                $tokenColonPtr = $phpcsFile->findPrevious(T_COLON, $tokenReturnTypePtr);
                $tokenNullablePtr = $phpcsFile->findPrevious(T_NULLABLE, $tokenReturnTypePtr, $stackPtr);

                $phpcsFile->fixer->replaceToken($tokenColonPtr, '');
                $phpcsFile->fixer->replaceToken($tokenReturnTypePtr, '');

                if ($tokenNullablePtr) {
                    $phpcsFile->fixer->replaceToken($tokenNullablePtr, '');
                }
            }
        }
    }
}
