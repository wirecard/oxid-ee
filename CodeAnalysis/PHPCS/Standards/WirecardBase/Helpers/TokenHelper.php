<?php

/**
 * Utility methods for handling PHPCS Tokens.
 */
class WirecardBase_Helpers_TokenHelper
{
    /**
     * Returns the amount of lines of code for a given stack of tokens.
     */
    public static function getLinesOfCode(array $tokens, int $start = 0, int $end = null): int
    {
        $linesOfCode = 0;
        $currentLine = 0;
        $emptyTokens = array_values(PHP_CodeSniffer_Tokens::$emptyTokens);

        if ($end === null) {
            $end = count($tokens) - 1;
        }

        for ($i = $start; $i <= $end; $i++) {
            if (empty($tokens[$i])) {
                break;
            }

            if ($tokens[$i]['line'] !== $currentLine && !in_array($tokens[$i]['code'], $emptyTokens)) {
                $currentLine = $tokens[$i]['line'];
                $linesOfCode++;
            }
        }

        return $linesOfCode;
    }
}
