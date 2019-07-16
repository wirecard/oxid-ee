<?php

/**
 * Enforces early returns instead of using else/elseif statements.
 */
class WirecardBase_Sniffs_ControlStructures_ElseSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_ELSE,
            T_ELSEIF,
        ];
    }

    /**
     * @inheritdoc
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $phpcsFile->addWarning(
            'Usage of else/elseif is discouraged; use early return if possible',
            $stackPtr,
            'EnforceEarlyReturn'
        );
    }
}
