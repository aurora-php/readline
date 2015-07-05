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
 * Provides readline functionality either by using built-in readline
 * capabilities or by an emulation, if built-in functionality is not
 * available.
 *
 * @copyright   copyright (c) 2011-2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 *
 * @depends     \Octris\Readline\Bash
 * @depends     \Octris\Readline\Emulated
 * @depends     \Octris\Readline\Native
 * @depends     \Octris\IReadline
 */
class Readline
{
    /**
     * Size of history, maximum entries.
     */
    const T_HISTORY_SIZE = 100;

    /**
     * Class to use for new instance.
     *
     * @type    \Octris\Readline
     */
    protected static $class = null;

    /**
     * Instances of readline. Same history files share the sime readline instance.
     *
     * @type    array
     */
    protected static $instances = array();

    /**
     * Registered readline devices.
     *
     * @type    array
     */
    protected static $devices = array();

    /**
     * Register a readline device.
     *
     * @param   string          $class                  Full qualified classname of the device.
     * @param   int             $priority               (Optional) priority for device.
     */
    public static function registerDevice($class, $priority = 0)
    {
        self::$devices[$class] = $priority;
    }

    /**
     * Returns a new instance of readline. Note that no history functionality is available, if no
     * history path is provided.
     *
     * @param   string          $history_file           Optional path to a history file.
     * @return  \Octris\Readline                        Instance of readline.
     */
    public static function getInstance($history_file = '')
    {
        if (!isset(self::$instances[$history_file])) {
            if (is_null(self::$class)) {
                // detect and decide wich readline device to use
                arsort(self::$devices);

                foreach (self::$devices as $device => $priority) {
                    if (in_array('Octris\IReadline', class_implements($device))
                        && $device::detect()) {
                        self::$class = $device;
                        break;
                    }
                }
            }

            self::$instances[$history_file] = new self::$class($history_file);
        }

        return self::$instances[$history_file];
    }

    /** no need to ever create an instance of this class **/
    protected function __construct()
    {
    }
    protected function __clone()
    {
    }
}

readline::registerDevice('\Octris\Readline\Native', -1);
readline::registerDevice('\Octris\Readline\Bash', -2);
readline::registerDevice('\Octris\Readline\Emulated', -3);
