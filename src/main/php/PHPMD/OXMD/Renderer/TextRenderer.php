<?php
/**
 * This file is part of the PHP Mess Detector OXID extension.
 *
 * PHP Version 5
 *
 * Copyright (c) 2014, Manuel Pichler <mapi@phpmd.org>.
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

namespace PHPMD\OXMD\Renderer;

use PHPMD\AbstractRenderer;
use PHPMD\OXMD\Certification\ExtremeValue;
use PHPMD\OXMD\Result\MetricExtremeValue;
use PHPMD\OXMD\Rule\Coverage;
use PHPMD\OXMD\Rule\CrapIndex;
use PHPMD\OXMD\Rule\CyclomaticComplexity;
use PHPMD\OXMD\Rule\NpathComplexity;
use PHPMD\OXMD\Struct\Metric;
use PHPMD\Report;
use PHPMD\RuleViolation;

/**
 * This renderer output a textual log with all found violations and suspect
 * software artifacts.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   @project.version@
 */
class TextRenderer extends AbstractRenderer
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
     * This method will be called when the engine has finished the source analysis
     * phase.
     *
     * @param \PHPMD\Report $report
     * @return void
     */
    public function renderReport(Report $report)
    {
        $writer = $this->getWriter();
        $writer->write(PHP_EOL);

        foreach ($report->getRuleViolations() as $violation) {
            $this->_extractMetric($violation);

            $this->renderLine('=');
            $writer->write('FILE: ...' . substr($violation->getFileName(), -63, 63));
            $writer->write(' (' . $violation->getBeginLine() . ')');
            $writer->write(PHP_EOL);
            $this->renderLine('=');
            $writer->write(wordwrap($violation->getDescription(), 80));
            $writer->write(PHP_EOL);
            $writer->write(PHP_EOL);
        }
    }

    /**
     * Prints the oxid report summary with the expected module costs.
     * 
     * @return void
     */
    public function end()
    {
        $writer = $this->getWriter();
        $this->renderLine('=');
        $writer->write(' Oxid Module Certification');
        $writer->write('                        Value');
        $writer->write('                  Factor');
        $writer->write(PHP_EOL);
        $this->renderLine('=');

        $this->renderExtremeValue('Code Coverage', $this->coverage);
        $this->renderLine('-');
        $this->renderExtremeValue('C.R.A.P Index', $this->crapIndex);
        $this->renderLine('-');
        $this->renderExtremeValue('NPath Complexity', $this->npath);
        $this->renderLine('-');
        $this->renderExtremeValue('Cyclomatic Complexity', $this->ccn);

        $price = $this->_calculatePrice();

        $this->renderLine('=');
        $writer->write(str_repeat(' ', 55) . 'Factor:  ');
        $writer->write(sprintf('% 15s', number_format($this->_calculateFactor(), 2, ',', '.')));
        $writer->write(PHP_EOL);
        $this->renderLine('=');
        $writer->write(str_repeat(' ', 55) . 'Price:   ');
        $writer->write(sprintf('% 15s', number_format($price, 2, ',', '.')) . PHP_EOL);
        $this->renderLine('=');
    }

    private function renderExtremeValue($label, ExtremeValue $extremeValue)
    {
        $writer = $this->getWriter();

        $writer->write(str_pad(" {$label}", 45, ' ', STR_PAD_RIGHT));
        $writer->write(sprintf('% 10.2f', $extremeValue->getValue()));
        $writer->write('                 ');
        $writer->write(sprintf('% 7.2f', $extremeValue->getFactor()));
        $writer->write(PHP_EOL);
        foreach ($extremeValue->getFiles() as $file => $line) {

            $writer->write($this->_getPathName($file, 64));
            $writer->write(' (' . $line . ')');
            $writer->write(PHP_EOL);
        }
    }

    private function renderLine($char)
    {
        $this->getWriter()->write(str_repeat($char, 80) . PHP_EOL);
    }

    /**
     * Returns a shortened path name with a maximum of <b>$length</b>.
     *
     * @param string  $path   The raw input path.
     * @param integer $length The maximum path length.
     * 
     * @return string
     */
    private function _getPathName($path, $length)
    {
        $path = "  {$path}";
        if (strlen($path) + 4 <= $length) {
            return $path;
        }
        return '  …' . substr($path, -1 * $length, $length);
    }

    /**
     * Calclates the total factor/multiplier for the module costs.
     *
     * @return integer
     */
    private function _calculateFactor()
    {
        return (
            $this->coverage->getFactor() *
            $this->crapIndex->getFactor() *
            $this->npath->getFactor() *
            $this->ccn->getFactor()
        );
    }

    /**
     * Total price for the currently analyzed module.
     * 
     * @return float
     */
    private function _calculatePrice()
    {
        return 119 + ($this->_calculateFactor() * 200);
    }

    /**
     * Extracts some additional data from the given rule violation.
     *
     * @param \PHPMD\RuleViolation $violation
     * @return void
     */
    private function _extractMetric(RuleViolation $violation)
    {
        $rule = $violation->getRule();
        if ($rule instanceof CyclomaticComplexity) {
            $this->ccn->update($violation);
        }
        if ($rule instanceof NpathComplexity) {
            $this->npath->update($violation);
        }
        if ($rule instanceof Coverage) {
            $this->coverage->update($violation);
        }
        if ($rule instanceof CrapIndex) {
            $this->crapIndex->update($violation);
        }
    }
}
