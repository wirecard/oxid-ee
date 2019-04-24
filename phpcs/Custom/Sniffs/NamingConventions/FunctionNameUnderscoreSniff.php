<?php

/**
 * Ensures that all private and protected methods start with an underscore and public methods do not.
 */
class Custom_Sniffs_NamingConventions_FunctionNameUnderscoreSniff implements PHP_CodeSniffer_Sniff
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
        $tokenFunctionNamePosition = $phpcsFile->findNext(T_STRING, $stackPtr);
        $tokenFunctionName = $tokens[$tokenFunctionNamePosition];
        $tokenVisibilityPosition = $phpcsFile->findPrevious([T_PRIVATE, T_PROTECTED, T_PUBLIC], $stackPtr);
        $tokenVisibility = $tokens[$tokenVisibilityPosition];

        switch ($tokenVisibility['code']) {
            case T_PRIVATE:
                if ($tokenFunctionName['content'][0] !== '_') {
                    $fix = $phpcsFile->addFixableError(
                        'Private functions must start with an underscore',
                        $tokenFunctionNamePosition,
                        'PrivateFunctionNameUnderscore'
                    );

                    if ($fix) {
                        $phpcsFile->fixer->addContentBefore($tokenFunctionNamePosition, '_');
                    }
                }

                break;
            case T_PROTECTED:
                if ($tokenFunctionName['content'][0] !== '_') {
                    $fix = $phpcsFile->addFixableError(
                        'Protected functions must start with an underscore',
                        $tokenFunctionNamePosition,
                        'ProtectedFunctionNameUnderscore'
                    );

                    if ($fix) {
                        $phpcsFile->fixer->addContentBefore($tokenFunctionNamePosition, '_');
                    }
                }

                break;
            case T_PUBLIC:
                // this check excludes magic methods (e.g. __construct)
                if ($tokenFunctionName['content'][0] === '_' && $tokenFunctionName['content'][1] !== '_') {
                    $fix = $phpcsFile->addFixableError(
                        'Public functions must not start with an underscore',
                        $tokenFunctionNamePosition,
                        'PublicFunctionNameUnderscore'
                    );

                    if ($fix) {
                        $phpcsFile->fixer->substrToken($tokenFunctionNamePosition, 1);
                    }
                }

                break;
        }
    }
}
