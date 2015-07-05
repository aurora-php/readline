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
 * Use bash for readline support.
 *
 * @copyright   copyright (c) 2011-2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Bash implements \Octris\IReadline
{
    /**
     * Whether the readline implementation supports an input history.
     *
     * @type    bool
     */
    protected static $has_history = false;

    /**
     * Set bash command.
     *
     * @type    string
     */
    protected static $cmd = '';

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
    }

    /**
     * Detect bash readline support.
     *
     * @return  bool                    Whether readline using bash is supported.
     */
    public static function detect()
    {
        if (($detected = !!($cmd = exec('which bash')))) {
            if (($detected = (preg_match('/builtin/', exec($cmd . ' -c "type type"')) &&
                              preg_match('/builtin/', exec($cmd . ' -c "type read"'))))) {
                self::$has_history = preg_match('/builtin/', exec($cmd . ' -c "type history"'));

                self::$cmd = $cmd;
            }
        }

        return $detected;
    }

    /**
     * Register a completion function.
     *
     * @param   callable        $callback               Callback to call for completion.
     */
    public function setCompletion(callable $callback)
    {
    }

    /**
     * Get user input from STDIN.
     *
     * @param   string      $prompt     Optional prompt to print.
     * @return  string                  User input.
     */
    public function readline($prompt = '')
    {
        if ($this->history_file != '') {
            // input supports history
            $cmd = sprintf(
                '%s -c "history -r %s; CMD=""; read -ep %s CMD; history -s \$CMD; history -w %s; echo \$CMD"',
                self::$cmd,
                escapeshellarg($this->history_file),
                escapeshellarg($prompt),
                escapeshellarg($this->history_file)
            );
        } else {
            // input does not support history
            $cmd = sprintf(
                '%s -c "CMD=""; read -ep %s CMD; echo \$CMD"',
                self::$cmd,
                escapeshellarg($prompt)
            );
        }

        $return = exec($cmd);

        return $return;
    }
}
