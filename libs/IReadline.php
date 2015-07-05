<?php

/*
 * This file is part of the 'octris/readline' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris;

/**
 * Interface for readline devices.
 *
 * @copyright   copyright (c) 2013-2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
interface IReadline
{
    /**
     * Detect if the device is supported by the system. It's required that this
     * method returns either 'false' if the device is not supported by the system
     * or 'true'.
     *
     * @return  bool                                Whether the device is supported.
     */
    public static function detect();

    /**
     * Main readline function with optional prompt.
     *
     * @param   string              $prompt         Optional prompt to display.
     * @return  string                              Entered value.
     */
    public function readline($prompt = '');

    /**
     * Register a completion function. If the device is unable to support input
     * completion, just leave the method body empty.
     *
     * @param   callable            $callback       Callback to call for completion.
     */
    public function setCompletion(callable $callback);
}
