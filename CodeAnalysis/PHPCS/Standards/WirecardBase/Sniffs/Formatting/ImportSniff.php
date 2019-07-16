<?php

/**
 * Ensures that use statements are properly formatted.
 */
class WirecardBase_Sniffs_Formatting_ImportSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_OPEN_TAG];
    }

    /**
     * @inheritdoc
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $importTokenList = $this->getOrderedImportTokenList($phpcsFile);
        $fix = false;

        foreach ($importTokenList as $i => $listItem) {
            $tokenUsePtr = array_keys($listItem['tokens'])[0];
            $tokenPrevUsePtr = $i > 0 ? array_keys($importTokenList[$i - 1]['tokens'])[0] : null;
            $tokenPrecUsePtr = $phpcsFile->findPrevious(T_USE, $tokenUsePtr - 1);

            // check order
            if ($tokenPrecUsePtr !== false) {
                if ($tokenPrevUsePtr === null || ($tokenPrevUsePtr !== null && $tokenPrevUsePtr !== $tokenPrecUsePtr)) {
                    $fix = $phpcsFile->addFixableWarning(
                        'Use statements must be ordered alphabetically',
                        $tokenPrecUsePtr ?? $tokenUsePtr,
                        'WrongImportOrder'
                    );
                }
            }

            // check grouping
            if ($listItem['newline'] && $tokens[$tokenUsePtr - 2]['code'] !== T_WHITESPACE) {
                $fix = $phpcsFile->addFixableWarning(
                    'Use statements with equal base names should be grouped',
                    $tokenUsePtr,
                    'NoImportGroup'
                );
            }
        }

        // run fixer
        if ($fix) {
            foreach ($importTokenList as $i => $listItem) {
                $j = 0;

                foreach ($listItem['tokens'] as $stackPtr => $token) {
                    $content = '';

                    // remove all tokens except for the very last one, which is replaced to maintain positions
                    if ($i === count($importTokenList) - 1 && $j === count($listItem['tokens']) - 1) {
                        foreach ($importTokenList as $listItem) {
                            if ($listItem['newline']) {
                                $content .= "\n";
                            }

                            $content .= "use {$listItem['path']};\n";
                        }
                    }

                    $phpcsFile->fixer->replaceToken($stackPtr, $content);

                    $j++;
                }
            }
        }
    }

    /**
     * Returns a sorted array of tokens associated with imports.
     */
    private function getOrderedImportTokenList(PHP_CodeSniffer_File $phpcsFile)
    {
        $importTokenList = [];
        $tokens = null;

        foreach ($phpcsFile->getTokens() as $stackPtr => $token) {
            // after a line is processed, add it to the list
            if ($tokens !== null && $token['line'] !== reset($tokens)['line']) {
                $importTokenList[] = [
                    'path' => $this->getPath($tokens),
                    'tokens' => $tokens,
                ];

                $tokens = null;
            }

            // start the collection for new statements
            if ($token['code'] === T_USE && $token['level'] === 0) {
                $tokens = [];
            }

            // add the token to the collection if there is one
            if ($tokens !== null) {
                $tokens[$stackPtr] = $token;
            }
        }

        // sort the list alphabetically
        uasort($importTokenList, function ($a, $b) {
            return strcasecmp($a['path'], $b['path']);
        });
        $importTokenList = array_values($importTokenList);

        // add a `newline` flag
        foreach ($importTokenList as $i => &$listItem) {
            $currentPathBase = explode('\\', $listItem['path'])[0];
            $previousPathBase = $i ? explode('\\', $importTokenList[$i - 1]['path'])[0] : $currentPathBase;

            $listItem['newline'] = $listItem['path'] !== $currentPathBase && $currentPathBase !== $previousPathBase;
        }

        return $importTokenList;
    }

    /**
     * Returns a path of an array of tokens.
     */
    private function getPath(array $tokens)
    {
        $path = '';

        foreach ($tokens as $token) {
            if (in_array($token['code'], [T_USE, T_WHITESPACE, T_SEMICOLON])) {
                continue;
            }

            $path .= $token['content'];
        }

        return $path;
    }
}
