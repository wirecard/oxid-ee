<?php

/**
 * Warns if PHP syntax not supported by OXID or the OXID testing framework is being used.
 */
class Custom_Sniffs_OXID_PhpSyntaxSniff implements PHP_CodeSniffer_Sniff
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
        for ($i = $tokenFunction['parenthesis_opener']; $i < $tokenFunction['parenthesis_closer']; $i++) {
            if ($tokens[$i]['code'] === T_STRING || $tokens[$i]['code'] === T_ARRAY_HINT) {
                $fix = $phpcsFile->addFixableWarning('Type hints should not be used', $i, 'TypeHint');

                if ($fix) {
                    $phpcsFile->fixer->replaceToken($i, '');
                }
            }
        }

        // check for return types
        $tokenReturnTypePosition = isset($tokenFunction['scope_opener']) ? $phpcsFile->findNext(
            T_RETURN_TYPE,
            $tokenFunction['parenthesis_closer'],
            $tokenFunction['scope_opener']
        ) : null;

        if ($tokenReturnTypePosition) {
            $fix = $phpcsFile->addFixableWarning(
                'Return types should not be used',
                $tokenReturnTypePosition,
                'ReturnType'
            );

            if ($fix) {
                $tokenColonPosition = $phpcsFile->findPrevious(T_COLON, $tokenReturnTypePosition);

                $phpcsFile->fixer->replaceToken($tokenColonPosition, '');
                $phpcsFile->fixer->replaceToken($tokenReturnTypePosition, '');
            }
        }
    }
}
