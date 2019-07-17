<?php

/**
 * Ensures that only custom exceptions are thrown.
 */
class WirecardOxid_Sniffs_Classes_CustomExceptionSniff implements PHP_CodeSniffer_Sniff
{
    const FORBIDDEN_CLASSES = [
        Exception::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_NEW];
    }

    /**
     * @inheritdoc
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $tokenClassNamePtr = $phpcsFile->findNext(T_STRING, $stackPtr);
        $tokenClassName = $tokenClassNamePtr ? $tokens[$tokenClassNamePtr] : null;

        if ($tokenClassName && in_array($tokenClassName['content'], self::FORBIDDEN_CLASSES)) {
            $phpcsFile->addError(
                'Exception class %s must not be thrown (use custom exception)',
                $tokenClassNamePtr,
                'ForbiddenExceptionClass',
                [$tokenClassName['content']]
            );
        }
    }
}
