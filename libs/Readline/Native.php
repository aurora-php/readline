<?php

/*
 * This file is part of the 'octris/readline' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Readline;

/**
 * Wrapper for native (built-in) readline functionality.
 *
 * @copyright   copyright (c) 2011-2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 *
 * @depends     \Octris\Readline
 * @depends     \Octris\IReadline
 */
class Native implements \Octris\IReadline
{
    /**
     * Instance counter.
     *
     * @type    int
     */
    private static $instances = 0;

    /**
     * Instance number.
     *
     * @type    int
     */
    private $instance_id = 0;

    /**
     * Whether the readline implementation supports an input history.
     *
     * @type    bool
     */
    protected static $has_history = false;

    /**
     * Name of history file that was used for previous call to readline.
     *
     * @type    string
     */
    private static $last_history = '';

    /**
     * Last used instance.
     *
     * @type    int
     */
    private static $last_instance = 0;

    /**
     * Completion function.
     *
     * @type    null|callable
     */
    protected $completion_callback = null;

    /**
     * History file bound to instance of readline. If no file is specified, the history will not be used.
     *
     * @type    string
     */
    protected $history_file = '';

    /**
     * Constructor.
     *
     * @param   string          $history_file               History file to use for this readline instance.
     */
    public function __construct($history_file = '')
    {
        $this->history_file = (self::$has_history ? $history_file : '');
        $this->instance_id  = ++self::$instances;
    }

    /**
     * Detect native readline support.
     *
     * @return  bool                    Whether native readline is supported.
     */
    public static function detect()
    {
        $history = false;

        if (($detected = function_exists('readline'))) {
            self::$has_history = function_exists('readline_read_history');
        }

        return $detected;
    }

    /**
     * Destructor writes history to history file.
     */
    public function __destruct()
    {
        if ($this->history_file != '') {
            readline_write_history($this->history_file);
        }
    }

    /**
     * Register a completion function.
     *
     * @param   callable        $callback               Callback to call for completion.
     */
    public function setCompletion(callable $callback)
    {
        $this->completion_callback = $callback;
    }

    /**
     * Switch readline instance settings. Changes history if there are multiple
     * readline instances with different history files and changes completion callback for
     * different readline instances.
     */
    protected function switchSettings()
    {
        if ($this->instance_id != self::$last_instance) {
            // switch instance settings
            if (is_null($this->completion_callback)) {
                readline_completion_function(function ($input, $index) {
                });
            } else {
                readline_completion_function(function ($input, $index) {
                    return $this->complete($input, $index, $this->completion_callback);
                });
            }

            if ($this->history_file != self::$last_history) {
                // change history
                readline_write_history(self::$last_file);
                readline_clear_history();

                if ($this->history_file != '') {
                    readline_read_history($this->history_file);
                }

                self::$last_history = $this->history_file;
            }

            self::$last_instance = $this->instance_id;
        }
    }

    /**
     * Add string to the history file.
     *
     * @param   string      $line       Line to add to the history file.
     */
    protected function addHistory($line)
    {
        if ($this->history_file) {
            readline_add_history($line);
        }
    }

    /**
     * Completion main function.
     *
     * @param   string      $input      Input from readline.
     * @param   string      $index      Position in line where completion was initiated.
     * @param   callable    $callback   A callback to call for processing completion.
     * @return  array                   Matches.
     */
    public function complete($input, $index, callable $callback)
    {
        $info = readline_info();
        $line = substr($info['line_buffer'], 0, $info['end']);

        foreach ($callback($input, $line) as $match) {
            $matches[] = substr($match, $index);
        }

        return $matches;
    }

    /**
     * Get user input using native readline extension.
     *
     * @param   string      $prompt     Optional prompt to print.
     * @return  string                  User input.
     */
    public function readline($prompt = '')
    {
        $this->switchSettings();

        $return = ltrim(\readline($prompt));

        $this->addHistory($return);

        return $return;
    }
}
