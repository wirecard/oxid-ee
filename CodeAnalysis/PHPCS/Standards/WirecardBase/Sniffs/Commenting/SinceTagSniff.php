<?php

/**
 * Ensures that DocBlocks contain a @since tag.
 */
class WirecardBase_Sniffs_Commenting_SinceTagSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns token types required to have a @since tag.
     */
    public function getRequiredTypes()
    {
        return [
            T_CLASS,
            T_PUBLIC,
            T_PROTECTED,
            T_PRIVATE,
        ];
    }

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_DOC_COMMENT_OPEN_TAG];
    }

    /**
     * @inheritdoc
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $tokenNextPtr = $phpcsFile->findNext(T_WHITESPACE, $tokens[$stackPtr]['comment_closer'] + 1, null, true);
        $tokenNext = $tokenNextPtr ? $tokens[$tokenNextPtr] : null;
        $hasSinceTag = false;

        // bail if there is no next token or it is not one of the required types
        if (!$tokenNext || !in_array($tokenNext['code'], $this->getRequiredTypes())) {
            return;
        }

        foreach ($tokens[$stackPtr]['comment_tags'] as $tokenTagPtr) {
            if ($tokens[$tokenTagPtr]['content'] === '@since') {
                $hasSinceTag = true;

                break;
            }
        }

        if (!$hasSinceTag) {
            $phpcsFile->addError('Missing @since tag in DocBlock', $stackPtr, 'NoSinceTag');
        }
    }
}
