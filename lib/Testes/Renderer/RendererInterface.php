<?php

namespace Testes\Renderer;
use Testes\Test\Reporter\ReporterInterface;

/**
 * Main interface for output renderers.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
interface RendererInterface
{
    /**
     * Renders the test results.
     * 
     * @param TestInterface $test The test to output.
     * 
     * @return string
     */
    public function render(ReporterInterface $test);
}
