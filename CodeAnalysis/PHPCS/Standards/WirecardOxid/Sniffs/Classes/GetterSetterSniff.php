<?php

/**
 * Ensures that getters and setters are used for certain properties.
 */
class WirecardOxid_Sniffs_Classes_GetterSetterSniff implements PHP_CodeSniffer_Sniff
{
    const FORBIDDEN_PROPERTIES = [
        '_aViewData',
    ];

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
        $tokenProperty = $tokens[$stackPtr + 1];

        if (in_array($tokenProperty['content'], self::FORBIDDEN_PROPERTIES)) {
            $phpcsFile->addError(
                'Direct access to %s not allowed; use a getter/setter',
                $stackPtr,
                $tokenProperty['content'],
                $tokenProperty['content']
            );
        }
    }
}
