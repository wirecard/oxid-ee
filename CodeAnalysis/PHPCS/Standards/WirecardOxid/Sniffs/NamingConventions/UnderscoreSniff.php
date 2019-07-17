<?php

/**
 * Ensures that private/protected properties and methods start with an underscore and public ones do not.
 */
class WirecardOxid_Sniffs_NamingConventions_UnderscoreSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_PRIVATE,
            T_PROTECTED,
            T_PUBLIC,
        ];
    }

    /**
     * @inheritdoc
     *
     * @since 1.2.0
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $tokenNamePosition = $phpcsFile->findNext([T_VARIABLE, T_STRING], $stackPtr);

        if (!$tokenNamePosition) {
            return;
        }

        $tokenName = $tokens[$tokenNamePosition];

        switch ($tokens[$stackPtr]['code']) {
            case T_PRIVATE:
            case T_PROTECTED:
                if (!$this->tokenStartsWithUnderscore($tokenName)) {
                    $visibilityString = $tokens[$stackPtr]['code'] === T_PRIVATE ? 'Private' : 'Protected';

                    $fix = $phpcsFile->addFixableError(
                        "{$visibilityString} variables and functions must start with an underscore",
                        $tokenNamePosition,
                        "{$visibilityString}NameUnderscore"
                    );

                    if ($fix) {
                        if ($tokenName['code'] === T_VARIABLE) {
                            $phpcsFile->fixer->replaceToken(
                                $tokenNamePosition,
                                substr_replace($tokenName['content'], '_', 1, 0)
                            );
                        } else {
                            $phpcsFile->fixer->addContentBefore($tokenNamePosition, '_');
                        }
                    }
                }

                break;
            case T_PUBLIC:
                if ($this->tokenStartsWithUnderscore($tokenName)) {
                    $fix = $phpcsFile->addFixableError(
                        'Public variables and functions must not start with an underscore',
                        $tokenNamePosition,
                        'PublicNameUnderscore'
                    );

                    if ($fix) {
                        if ($tokenName['code'] === T_VARIABLE) {
                            $phpcsFile->fixer->replaceToken(
                                $tokenNamePosition,
                                substr_replace($tokenName['content'], '', 1, 1)
                            );
                        } else {
                            $phpcsFile->fixer->substrToken($tokenNamePosition, 1);
                        }
                    }
                }

                break;
        }
    }

    /**
     * Checks if a given token's name starts with an underscore.
     *
     * @return bool
     */
    private function tokenStartsWithUnderscore($token)
    {
        return $token['content'] && preg_match('/^\$?_[^_]/', $token['content']);
    }
}
