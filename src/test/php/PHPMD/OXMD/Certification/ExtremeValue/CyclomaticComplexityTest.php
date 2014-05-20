<?php
/**
 * This file is part of the PHP Mess Detector OXID extension.
 *
 * PHP Version 5
 *
 * Copyright (c) 2008-2012, Manuel Pichler <mapi@phpmd.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   @project.version@
 */

namespace PHPMD\OXMD\Certification\ExtremeValue;

use PHPMD\OXMD\Certification\ExtremeValueTest;

class CyclomaticComplexityTest extends ExtremeValueTest
{
    /**
     * @test
     * @dataProvider get_factor_and_threshold
     */
    public function factor_calculation($value, $threshold, $expected)
    {
        $extremeValue = $this->create_extreme_value($value, $threshold);

        $this->assertEquals($expected, $extremeValue->getFactor(), '', 0.00001);
    }

    public function get_factor_and_threshold()
    {
        return array(
            array(50, null, 47),
            array(25, null, 22),
            array(10, null, 7),
            array(5, null, 2),
            array(4, null, 1),
            array(3, null, 1),
            array(2, null, 1),
            array(1, null, 1),
            array(0, null, 1),

            array(25, 10, 16),
            array(11, 10, 2),
            array(10, 10, 1),
            array(9, 10, 1),
        );
    }

    /**
     * @param int|float $value
     * @param int|float $threshold
     * @return \PHPMD\OXMD\Certification\ExtremeValue\CyclomaticComplexity
     */
    protected function create_extreme_value($value, $threshold)
    {
        return parent::create_extreme_value($value, $threshold);
    }
}
