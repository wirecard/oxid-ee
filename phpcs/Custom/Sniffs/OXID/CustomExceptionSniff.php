<?php

/**
 * Ensures that only custom exceptions are thrown.
 *
 * @see https://docs.oxid-esales.com/developer/en/6.0/modules/certification/software_quality.html#exception-handling
 *
 * @since 1.0.1
 */
class Custom_Sniffs_OXID_CustomExceptionSniff implements PHP_CodeSniffer_Sniff
{
    const FORBIDDEN_EXCEPTION_CLASSES = [
        Exception::class,
    ];

    /**
     * @inheritdoc
     *
     * @since 1.0.1
     */
    public function register()
    {
        return [T_NEW];
    }

    /**
     * @inheritdoc
     *
     * @since 1.0.1
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $tokenClassName = $tokens[$phpcsFile->findNext(T_STRING, $stackPtr)];

        if (in_array($tokenClassName['content'], self::FORBIDDEN_EXCEPTION_CLASSES)) {
            $phpcsFile->addError(
                'Exception class %s must not be thrown (use custom exception)',
                $stackPtr,
                'ForbiddenExceptionClass',
                [$tokenClassName['content']]
            );
        }
    }
}
