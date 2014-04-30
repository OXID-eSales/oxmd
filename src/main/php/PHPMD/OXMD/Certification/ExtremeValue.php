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

use PHPMD\RuleViolation;

/**
 * Class used to collect the extreme value in a violation stream.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   @project.version@
 */
abstract class ExtremeValue
{
    /**
     * This class should handle the minimum as an extreme value.
     */
    const MEASURE_MIN = 0;

    /**
     * This class should handle the maximum as an extreme value.
     */
    const MEASURE_MAX = 1;

    /**
     * The execution mode.
     *
     * @var integer
     */
    private $mode;

    /**
     * The raw metric value.
     *
     * @var mixed
     */
    private $value;

    /**
     * @var \PHPMD\RuleViolation[]
     */
    private $violations = array();

    /**
     * The threshold when the metric value has an impact on the factor calculation.
     *
     * @var mixed
     */
    private $threshold;

    /**
     * @param integer $mode
     * @param integer $threshold
     */
    public function __construct($mode, $threshold)
    {
        $this->mode = $mode;
        $this->value = $threshold;
        $this->threshold = $threshold;
    }

    /**
     * @return \PHPMD\RuleViolation[]
     */
    public function getViolations()
    {
        return $this->violations;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getThreshold()
    {
        return $this->threshold;
    }

    /**
     * Updates the internal metric extreme value.
     *
     * @param \PHPMD\RuleViolation $violation
     * @return void
     */
    public function update(RuleViolation $violation)
    {
        if (self::MEASURE_MAX === $this->mode) {
            $this->updateMax($violation);
        }
        if (self::MEASURE_MIN === $this->mode) {
            $this->updateMin($violation);
        }
    }

    private function updateMax(RuleViolation $violation)
    {
        if ($this->value > $violation->getMetric()) {
            return;
        }
        if (abs($this->value - $violation->getMetric()) < 0.00001) {
            return;
        }

        $this->value = $violation->getMetric();
        $this->violations[] = $violation;
    }

    private function updateMin(RuleViolation $violation)
    {
        if ($this->value < $violation->getMetric()) {
            return;
        }
        if (abs($this->value - $violation->getMetric()) < 0.00001) {
            return;
        }

        $this->value = $violation->getMetric();
        $this->violations[] = $violation;
    }

    /**
     * Returns the factor used to calculate the certification costs.
     *
     * @return float
     */
    abstract public function getFactor();
}
