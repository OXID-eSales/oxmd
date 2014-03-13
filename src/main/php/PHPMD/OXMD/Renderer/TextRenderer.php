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
use PHPMD\OXMD\Certification\CertificationCost;
use PHPMD\OXMD\Certification\ExtremeValue;
use PHPMD\OXMD\Certification\ExtremeValues;
use PHPMD\OXMD\Result\MetricExtremeValue;
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
     * @var \PHPMD\OXMD\Certification\CertificationCost
     */
    private $certificationCost;

    /**
     * @var \PHPMD\OXMD\Certification\ExtremeValues
     */
    private $extremeValues;

    public function __construct()
    {
        $this->certificationCost = new CertificationCost();
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
        $this->extremeValues = ExtremeValues::createFromViolations($report->getRuleViolations());

        $writer = $this->getWriter();
        $writer->write(PHP_EOL);

        foreach ($report->getRuleViolations() as $violation) {
            $this->renderLine('=');
            $this->renderPath($violation->getFileName(), $violation->getBeginLine());
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

        $this->renderExtremeValue('Code Coverage', $this->extremeValues->getCoverage());
        $this->renderLine('-');
        $this->renderExtremeValue('C.R.A.P Index', $this->extremeValues->getCrapIndex());
        $this->renderLine('-');
        $this->renderExtremeValue('NPath Complexity', $this->extremeValues->getNpath());
        $this->renderLine('-');
        $this->renderExtremeValue('Cyclomatic Complexity', $this->extremeValues->getCcn());

        $price = $this->certificationCost->calculate($this->extremeValues);

        $this->renderLine('=');
        $writer->write(str_repeat(' ', 55) . 'Factor:  ');
        $writer->write(sprintf('% 15s', number_format($this->extremeValues->calculateFactor(), 2, ',', '.')));
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
            $this->renderPath($file, $line);
        }
    }

    private function renderPath($path, $line)
    {
        $path = "  {$path}";
        if (strlen($path) + 4 > 64) {
            $path = '  …' . substr($path, -1 * 64, 64);
        }
        $this->getWriter()->write(sprintf('%s (%d)%s', $path, $line, PHP_EOL));
    }

    private function renderLine($char)
    {
        $this->getWriter()->write(str_repeat($char, 80) . PHP_EOL);
    }
}
