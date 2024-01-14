<?php

/**
 * Unit test class for the InterfaceNameSuffix sniff.
 *
 * @author  Anna Borzenko <annnechko@gmail.com>
 * @license https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\Generic\Tests\NamingConventions;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;
/**
 * Unit test class for the InterfaceNameSuffix sniff.
 *
 * @covers \PHP_CodeSniffer\Standards\Generic\Sniffs\NamingConventions\InterfaceNameSuffixSniff
 */
class InterfaceNameSuffixUnitTest extends AbstractSniffUnitTest
{
    /**
     * Returns the lines where errors should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of errors that should occur on that line.
     *
     * @return array<int, int>
     */
    public function getErrorList()
    {
        return [8 => 1];
    }
    //end getErrorList()
    /**
     * Returns the lines where warnings should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of warnings that should occur on that line.
     *
     * @return array<int, int>
     */
    public function getWarningList()
    {
        return [];
    }
    //end getWarningList()
}
//end class
