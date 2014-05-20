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

namespace PHPMD\OXMD\Certification;

use PHPMD\OXMD\Rule\Coverage;
use PHPMD\OXMD\Rule\CrapIndex;
use PHPMD\OXMD\Rule\CyclomaticComplexity;
use PHPMD\OXMD\Rule\NpathComplexity;

/**
 * Container class that holds the relevant extreme values for a module certification.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   @project.version@
 */
class ExtremeValues
{
    /**
     * @var \PHPMD\OXMD\Certification\ExtremeValue
     */
    private $ccn;

    /**
     * @var \PHPMD\OXMD\Certification\ExtremeValue
     */
    private $npath;

    /**
     * @var \PHPMD\OXMD\Certification\ExtremeValue
     */
    private $coverage;

    /**
     * @var \PHPMD\OXMD\Certification\ExtremeValue
     */
    private $crapIndex;

    public function __construct()
    {
        $this->ccn = new ExtremeValue\CyclomaticComplexity();
        $this->npath = new ExtremeValue\NpathComplexity();
        $this->coverage = new ExtremeValue\Coverage();
        $this->crapIndex = new ExtremeValue\CrapIndex();
    }

    /**
     * @return \PHPMD\OXMD\Certification\ExtremeValue
     */
    public function getCcn()
    {
        return $this->ccn;
    }

    /**
     * @return \PHPMD\OXMD\Certification\ExtremeValue
     */
    public function getCoverage()
    {
        return $this->coverage;
    }

    /**
     * @return \PHPMD\OXMD\Certification\ExtremeValue
     */
    public function getCrapIndex()
    {
        return $this->crapIndex;
    }

    /**
     * @return \PHPMD\OXMD\Certification\ExtremeValue
     */
    public function getNpath()
    {
        return $this->npath;
    }

    /**
     * Returns overall factor for all extreme values.
     *
     * @return float|int
     */
    public function calculateFactor()
    {
        return (
            $this->coverage->getFactor() *
            $this->crapIndex->getFactor() *
            $this->npath->getFactor() *
            $this->ccn->getFactor()
        );
    }

    /**
     * @param \Iterator|\PHPMD\RuleViolation[] $violations
     * @return \PHPMD\OXMD\Certification\ExtremeValues
     */
    public static function createFromViolations($violations)
    {
        $extremeValues = new self();

        foreach ($violations as $violation) {
            $rule = $violation->getRule();
            if ($rule instanceof CyclomaticComplexity) {
                $extremeValues->ccn->update($violation);
            }
            if ($rule instanceof NpathComplexity) {
                $extremeValues->npath->update($violation);
            }
            if ($rule instanceof Coverage) {
                $extremeValues->coverage->update($violation);
            }
            if ($rule instanceof CrapIndex) {
                $extremeValues->crapIndex->update($violation);
            }
        }
        return $extremeValues;
    }
}
