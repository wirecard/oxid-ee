<?php

/**
 * Ensures that getters and setters are used for certain properties.
 *
 * @see https://docs.oxid-esales.com/developer/en/6.0/modules/certification/software_quality.html#modules-certification-getters-setters
 */
class Custom_Sniffs_OXID_GetterSetterSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_OBJECT_OPERATOR];
    }

    /**
     * @inheritdoc
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $tokenThis = $tokens[$stackPtr];
        $tokenPrev = $tokens[$stackPtr - 1];
        $tokenNext = $tokens[$stackPtr + 1];

        if ($tokenPrev['content'] === '$this' && $tokenNext['content'] === '_aViewData') {
            $phpcsFile->addError(
                'Direct access to _aViewData not allowed; use getter/setter',
                $stackPtr,
                '_aViewData'
            );
        }
    }
}
