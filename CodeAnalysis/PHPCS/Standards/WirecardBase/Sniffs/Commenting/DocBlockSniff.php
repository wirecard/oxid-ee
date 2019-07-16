<?php

/**
 * Ensures that a DocBlock comments are used.
 */
class WirecardBase_Sniffs_Commenting_DocBlockSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_CLASS,
            T_INTERFACE,
            T_TRAIT,
            T_FUNCTION,
            T_VARIABLE,
        ];
    }

    /**
     * @inheritdoc
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $tokenEntry = $tokens[$stackPtr];

        if ($tokenEntry['code'] === T_VARIABLE) {
            $tokenContainingBlocksPtr = array_keys($tokenEntry['conditions'] ?? []);
            $tokenContainingBlock = count($tokenContainingBlocksPtr) === 1 ?
                $tokens[$tokenContainingBlocksPtr[0]]:
                null;

            if (!$tokenContainingBlock || !empty($tokenEntry['nested_parenthesis'])) {
                return;
            }
        }

        $tokenCommentPtr = $phpcsFile->findPrevious(T_DOC_COMMENT_CLOSE_TAG, $stackPtr);

        if (!$tokenCommentPtr || $tokens[$tokenCommentPtr]['line'] !== $tokens[$stackPtr]['line'] - 1) {
            $phpcsFile->addError(
                'Entry is missing DocBlock comment',
                $stackPtr,
                'NoDocBlock'
            );
        }
    }
}
