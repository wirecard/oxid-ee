<?php

/**
 * Ensures that docblocks contain a @since tag.
 */
class Custom_Sniffs_Commenting_SinceTagSniff implements PHP_CodeSniffer_Sniff
{
    const SUPPORTED_TYPES = [
        T_CLASS,
        T_PUBLIC,
        T_PROTECTED,
        T_PRIVATE,
    ];

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
        $tokenDocBlock = $tokens[$stackPtr];
        $tokenNextPosition = $phpcsFile->findNext(T_WHITESPACE, $tokenDocBlock['comment_closer'] + 1, null, true);
        $tokenNext = $tokenNextPosition ? $tokens[$tokenNextPosition] : null;

        // bail if there is no next token or it is not one of the supported types
        if (!$tokenNext || !in_array($tokenNext['code'], self::SUPPORTED_TYPES)) {
            return;
        }

        $hasSinceTag = false;

        foreach ($tokenDocBlock['comment_tags'] as $tokenTagPosition) {
            $tokenTag = $tokens[$tokenTagPosition];

            if ($tokenTag['content'] === '@since') {
                $hasSinceTag = true;

                break;
            }
        }

        if (!$hasSinceTag) {
            $phpcsFile->addError('Missing @since tag in comment', $stackPtr, 'NoSinceTag');
        }
    }
}
