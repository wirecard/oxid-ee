<?php

/**
 * Ensures that docblocks contain a @since tag.
 *
 * @since 1.1.0
 */
class Custom_Sniffs_Commenting_SinceTagSniff implements PHP_CodeSniffer_Sniff
{
    const SUPPORTED_TYPES = [
        T_CLASS,
        T_PUBLIC,
        T_PROTECTED,
        T_PRIVATE,
    ];

    private $version;

    /**
     * @inheritdoc
     *
     * @since 1.1.0
     */
    public function register()
    {
        $this->version = $this->getVersion();

        return [T_DOC_COMMENT_OPEN_TAG];
    }

    /**
     * @inheritdoc
     *
     * @since 1.1.0
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
            $fix = $phpcsFile->addFixableError('Missing @since tag in comment', $stackPtr, 'NoSinceTag');

            if ($fix) {
                $tokenWhiteSpace = $tokens[$tokenDocBlock['comment_closer'] - 1];

                $phpcsFile->fixer->addContentBefore(
                    $tokenDocBlock['comment_closer'],
                    '*' . PHP_EOL . $tokenWhiteSpace['content'] .
                    '* @since ' . $this->version . PHP_EOL . $tokenWhiteSpace['content']
                );
            }

        }
    }

    /**
     * Returns the module's version.
     *
     * @return string|null
     *
     * @since 1.1.0
     */
    private function getVersion()
    {
        $version = null;
        $sMetaDataFilePath = dirname(__FILE__, 5) . '/metadata.php';

        if (file_exists($sMetaDataFilePath)) {
            $sMetaDataFileContents = file_get_contents($sMetaDataFilePath);
            preg_match('/[\'"]version[\'"]\s*=>\s*[\'"]([\d\.]+)[\'"]/', $sMetaDataFileContents, $matches);

            if ($matches) {
                $version = $matches[1];
            }
        }

        return $version;
    }
}
