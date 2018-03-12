<?php

declare(strict_types=1);

/**
 * Checks the separation between methods in a class or interface.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2015 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */

namespace Sunfox\CodingStandard\Sniff\Whitespace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class FunctionSpacingSniff implements Sniff
{
    /**
     * The number of blank lines between functions.
     *
     * @var int
     */
    public $spacing = 1;

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_FUNCTION];
    }

    /**
     * Processes this sniff when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        /*
            Check the number of blank lines
            after the function.
        */

        if (isset($tokens[$stackPtr]['scope_closer']) === FALSE) {
            // Must be an interface method, so the closer is the semicolon.
            $closer = $phpcsFile->findNext(T_SEMICOLON, $stackPtr);
        } else {
            $closer = $tokens[$stackPtr]['scope_closer'];
        }

        //$this->spacing = end($tokens[$stackPtr]['conditions']) === T_INTERFACE ? 1 : 2;

        // Allow for comments on the same line as the closer.
        for ($nextLineToken = ($closer + 1); $nextLineToken < $phpcsFile->numTokens; $nextLineToken++) {
            if ($tokens[$nextLineToken]['line'] !== $tokens[$closer]['line']) {
                break;
            }
        }

        $foundLines = 0;
        if ($nextLineToken === ($phpcsFile->numTokens - 1)) {
            // We are at the end of the file.
            // Don't check spacing after the function because this
            // should be done by an EOF sniff.
            $foundLines = $this->spacing;
        } else {
            $nextContent = $phpcsFile->findNext(T_WHITESPACE, $nextLineToken, NULL, TRUE);
            if ($nextContent === FALSE) {
                // We are at the end of the file.
                // Don't check spacing after the function because this
                // should be done by an EOF sniff.
                $foundLines = $this->spacing;

            } elseif ($tokens[$nextContent]['code'] === T_CLOSE_CURLY_BRACKET) {
                // last function in class
                $foundLines = $this->spacing;

            } else {
                $foundLines += ($tokens[$nextContent]['line'] - $tokens[$nextLineToken]['line']);
            }
        }

        if ($foundLines !== $this->spacing) {
            $error = 'Expected %s blank line';
            if ($this->spacing !== 1) {
                $error .= 's';
            }

            $error .= ' after function; %s found';
            $data = [
                $this->spacing,
                $foundLines,
            ];

            $fix = $phpcsFile->addFixableError($error, $closer, 'After', $data);
            if ($fix === TRUE) {
                $phpcsFile->fixer->beginChangeset();
                for ($i = $nextLineToken; $i <= $nextContent; $i++) {
                    if ($tokens[$i]['line'] === $tokens[$nextContent]['line']) {
                        $phpcsFile->fixer->addContentBefore($i, str_repeat($phpcsFile->eolChar, $this->spacing));
                        break;
                    }

                    $phpcsFile->fixer->replaceToken($i, '');
                }

                $phpcsFile->fixer->endChangeset();
            }
        }

        /*
            Check the number of blank lines
            before the function.
        */

        $prevLineToken = NULL;
        for ($i = $stackPtr; $i > 0; $i--) {
            if (strpos($tokens[$i]['content'], $phpcsFile->eolChar) === FALSE) {
                continue;
            } else {
                $prevLineToken = $i;
                break;
            }
        }

        if (($prevLineToken === NULL) === TRUE) {
            // Never found the previous line, which means
            // there are 0 blank lines before the function.
            $foundLines = 0;
            $prevContent = 0;
        } else {
            $currentLine = $tokens[$stackPtr]['line'];

            $prevContent = $phpcsFile->findPrevious(T_WHITESPACE, $prevLineToken, NULL, TRUE);
            if ($tokens[$prevContent]['code'] === T_COMMENT) {
                // Ignore comments as they can have different spacing rules, and this
                // isn't a proper function comment anyway.
                return;
            }

            if ($tokens[$prevContent]['code'] === T_DOC_COMMENT_CLOSE_TAG
                && $tokens[$prevContent]['line'] === ($currentLine - 1)
            ) {
                // Account for function comments.
                $prevContent = $phpcsFile->findPrevious(T_WHITESPACE, ($tokens[$prevContent]['comment_opener'] - 1), NULL, TRUE);
            }

            if ($tokens[$prevContent]['code'] === T_OPEN_CURLY_BRACKET) {
                // first function in class
                return;
            }

            if ($tokens[$prevContent]['level'] && ($useToken = $phpcsFile->findPrevious(T_USE, $prevContent)) && $tokens[$useToken]['line'] === $tokens[$prevContent]['line']) {
                $this->spacing = 1; // method after 'use'
            }

            // Before we throw an error, check that we are not throwing an error
            // for another function. We don't want to error for no blank lines after
            // the previous function and no blank lines before this one as well.
            $prevLine = ($tokens[$prevContent]['line'] - 1);
            $i = ($stackPtr - 1);
            $foundLines = 0;
            while ($currentLine !== $prevLine && $currentLine > 1 && $i > 0) {
                if (isset($tokens[$i]['scope_condition']) === TRUE) {
                    $scopeCondition = $tokens[$i]['scope_condition'];
                    if ($tokens[$scopeCondition]['code'] === T_FUNCTION) {
                        // Found a previous function.
                        return;
                    }
                } elseif ($tokens[$i]['code'] === T_FUNCTION) {
                    // Found another interface function.
                    return;
                }

                $currentLine = $tokens[$i]['line'];
                if ($currentLine === $prevLine) {
                    break;
                }

                if ($tokens[($i - 1)]['line'] < $currentLine && $tokens[($i + 1)]['line'] > $currentLine) {
                    // This token is on a line by itself. If it is whitespace, the line is empty.
                    if ($tokens[$i]['code'] === T_WHITESPACE) {
                        $foundLines++;
                    }
                }

                $i--;
            }
        }

        if ($foundLines !== $this->spacing) {
            $error = 'Expected %s blank line';
            if ($this->spacing !== 1) {
                $error .= 's';
            }

            $error .= ' before function; %s found';
            $data = [
                $this->spacing,
                $foundLines,
            ];

            $fix = $phpcsFile->addFixableError($error, $stackPtr, 'Before', $data);
            if ($fix === TRUE) {
                if ($prevContent === 0) {
                    $nextSpace = 0;
                } else {
                    $nextSpace = $phpcsFile->findNext(T_WHITESPACE, ($prevContent + 1), $stackPtr);
                    if ($nextSpace === FALSE) {
                        $nextSpace = ($stackPtr - 1);
                    }
                }

                if ($foundLines < $this->spacing) {
                    $padding = str_repeat($phpcsFile->eolChar, ($this->spacing - $foundLines));
                    $phpcsFile->fixer->addContent($nextSpace, $padding);
                } else {
                    $nextContent = $phpcsFile->findNext(T_WHITESPACE, ($nextSpace + 1), NULL, TRUE);
                    $phpcsFile->fixer->beginChangeset();
                    for ($i = $nextSpace; $i < ($nextContent - 1); $i++) {
                        if (strpos($tokens[$i]['content'], "\n") !== FALSE) {
                            $phpcsFile->fixer->replaceToken($i, '');
                        }
                    }

                    $phpcsFile->fixer->replaceToken($i, str_repeat($phpcsFile->eolChar, $this->spacing + 1) . str_repeat(' ', $tokens[$i]['level'] * 4));
                    $phpcsFile->fixer->endChangeset();
                }
            }
        }
    }
}
