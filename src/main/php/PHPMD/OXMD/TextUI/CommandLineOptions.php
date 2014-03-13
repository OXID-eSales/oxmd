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

namespace PHPMD\OXMD\TextUI;

use PHPMD\OXMD\Renderer\TextRenderer;
use PHPMD\Rule;

/**
 * This is a helper class that collects the specified cli arguments and puts them
 * into accessible properties.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   @project.version@
 */
class CommandLineOptions extends \PHPMD\TextUI\CommandLineOptions
{
    /**
     * Constructs a new command line options instance.
     *
     * @param array $args
     * @param array $availableRuleSets
     * @throws \InvalidArgumentException
     */
    public function __construct(array $args, array $availableRuleSets = array())
    {
        // Remove current file name
        array_shift($args);

        $this->availableRuleSets = $availableRuleSets;

        $arguments = array();
        while (($arg = array_shift($args)) !== null) {
            switch ($arg) {

                case '--minimumpriority':
                    $this->minimumPriority = (int) array_shift($args);
                    break;

                case '--reportfile':
                    $this->reportFile = array_shift($args);
                    break;

                case '--inputfile':
                    array_unshift($arguments, $this->readInputFile(array_shift($args)));
                    break;

                case '--coverage':
                    $this->coverageReport = array_shift($args);
                    break;

                case '--suffixes':
                    $this->extensions = array_shift($args);
                    break;

                case '--exclude':
                    $this->ignore = array_shift($args);
                    break;

                case '--version':
                    $this->version = true;
                    return;

                case '--strict':
                    $this->strict = true;
                    break;

                default:
                    $arguments[] = $arg;
                    break;
            }
        }

        if (count($arguments) < 2) {
            throw new \InvalidArgumentException($this->usage(), self::INPUT_ERROR);
        }

        $arguments = array_pad($arguments, 3, null);

        $this->inputPath      = $arguments[0];
        $this->coverageReport = $arguments[1];
        $this->reportFormat   = $arguments[2] ?: 'text';
        $this->ruleSets       = realpath(__DIR__ . '/../../../../resources/rulesets/oxid.xml');
    }

    /**
     * @return \PHPMD\Renderer\TextRenderer
     */
    protected function createTextRenderer()
    {
        return new TextRenderer();
    }

    /**
     * Returns usage information for the PHPMD command line interface.
     *
     * @return string
     */
    public function usage()
    {
        return 'Usage:' . PHP_EOL .
               '  oxmd /path/to/src /path/to/clover.xml [report-format]' .
               PHP_EOL . PHP_EOL .
               'Arguments:' . PHP_EOL .
               '1) A php source code filename or directory. This argument can be' . PHP_EOL .
               '   a comma-separated string' . PHP_EOL .
               '2) The PHPUnit code coverage report generated with PHPUnit\'s' . PHP_EOL .
               '   command line option --coverage-clover' . PHP_EOL .
               '3) Optional report format' .
               PHP_EOL . PHP_EOL .
               'Available formats: xml, text, html.' .
               PHP_EOL . PHP_EOL .
               'Optional arguments that may be put after the mandatory arguments:' . PHP_EOL .
               '--minimumpriority: rule priority threshold; rules with lower priority' . PHP_EOL .
               '                   than this will not be used' . PHP_EOL .
               '--reportfile:      send report output to a file; default to STDOUT' . PHP_EOL .
               '--suffixes:        comma-separated string of valid source code filename ' . PHP_EOL .
               '                   extensions' . PHP_EOL .
               '--exclude:         comma-separated string of patterns that are used to ' . PHP_EOL .
               '                   ignore directories' . PHP_EOL .
               '--strict:          also report those nodes with a @SuppressWarnings ' . PHP_EOL .
               '                   annotation' . PHP_EOL;
    }
}
