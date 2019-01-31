<?php

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class FunctionNameUnderscoreSniff implements Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     * @return array
     */
    public function register(): array
    {
        return [T_FUNCTION];
    }

    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param PHP_CodeSniffer_File $file The file where the token was found.
     * @param integer $position  The position in the stack where the token was found.
     *
     * @return void
     */
    public function process(File $file, $position)
    {
        // gets the function name (is the next string token after the function token)
        $functionNamePosition = $file->findNext([T_STRING], $position);
        $functionNameToken = $file->getTokens()[$functionNamePosition];
        $functionName = $functionNameToken['content'];

        // gets the visibility keyword previous from the function token
        $visibilityKeywordPosition = $file->findPrevious([T_PRIVATE, T_PROTECTED, T_PUBLIC], $position);
        $visibilityKeywordToken = $file->getTokens()[$visibilityKeywordPosition];
        $visibilityKeyword = $visibilityKeywordToken['content'];

        switch ($visibilityKeyword) {
            case 'private':
                // private methods MUST start with an underscore
                if (strpos($functionName, '_') !== 0) {
                    $errorMsg = 'Private functions must start with an underscore';
                    $file->addFixableError($errorMsg, $position + 2, __CLASS__);
                    $file->fixer->addContent($position + 2, '_');
                }

                break;

            case 'protected':
                // protected methods MUST start with an underscore
                if (strpos($functionName, '_') !== 0) {
                    $errorMsg = 'Protected functions must start with an underscore';
                    $file->addFixableError($errorMsg, $position + 2, __CLASS__);
                    $file->fixer->addContent($position + 2, '_');
                }

                break;

            case 'public':
                // public methods MUST NOT start with an underscore (except for the '__construct' method)
                if (strpos($functionName, '_') === 0 && $functionName !== '__construct') {
                    $errorMsg = 'Public functions must NOT start with an underscore';
                    $file->addFixableError($errorMsg, $position + 2, __CLASS__);
                    $file->fixer->addContent($position + 2, '_');
                }

                break;

            default:
        }
    }
}
