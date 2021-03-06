<?php

/*
 * This file is part of IRIS-PHP, distributed under the General Public License version 3.
 * A copy of the GNU General Public Version 3 is readable in /library/gpl-3.0.txt.
 * More details about the copyright may be found at
 * <http://irisphp.org/copyright> or <http://www.gnu.org/licenses/>
 *  
 * @copyright 2011-2016 Jacques THOORENS
 */

class Messages {

    /**
     * 
     */
    const DEFAULT_LANGUAGE = 'Fr';

    /**
     * the list or recognized languages
     * @var string[]
     */
    public static $Languages = [
        'E' => 'En',
        'english' => 'En',
        'F' => 'Fr',
        'french' => 'Fr',
    ];
    public static $Error = [];
    public static $Help = [];

    /**
     * Displays, if possible, an help for the parameter $option
     * 
     * @param string $option the function (single character or full long option=
     */
    public static function Help($option) {
        // reads help file
        $format = '/CLI/Text/%s.php';
        if (count(self::$Help) == 0) {
            $hlpFile = sprintf($format, 'Hlp');
            CLI\FrontEnd::Loader($hlpFile);
        }
        if (is_bool($option) or $option == 'help') {
            $command = 'h';
        }
        elseif (strlen($option) > 1) {
            $command = array_search("$option:", CLI\Analyser::$Functions)[0];
        }
        else {
            $command = $option;
            if (isset(CLI\Analyser::$Functions[$option])) {
                $option = CLI\Analyser::$Functions[$option];
            }
            else {
                $option = CLI\Analyser::$Functions[$option . ':'];
            }
        }
        $language = CLI\Analyser::GetLanguage();
        echoLine(CLI\Parameters::DBLINE);
        echoLine('Iris-PHP --' . $option);
        Messages::Display(self::$Help[$language]['more']);
        echoLine(CLI\Parameters::DBLINE);
        if (!isset(self::$Help[$language][$command])) {
            Messages::Display('ERR_INHELP', $command);
        }
        else {
            Messages::Display(self::$Help[$language][$command]);
            //echo self::$help[$language][$command];
            $extension = self::$Help['Ext'][$command];
            while ($extension != 'TRUE' and $extension != 'FALSE') {
                Messages::Display(self::$Help[$language][$extension]);
                $extension = self::$Help['Ext'][$extension];
            }
        }
        echoLine(CLI\Parameters::DBLINE );
        die('');
    }

    /**
     * Display a var_dump between <pre> tags
     * 
     * @param mixed $var : a var or text to dump
     */
    public static function Dump($var) {
        var_dump($var);
    }

    /**
     * Displays a var_dump tags and optionaly dies
     *
     * @param mixed $var A printable message or variable
     * @param string $dieMessage
     * @param int $traceLevel If called from iris_debug, trace level is 1 instead of 0
     */
    public static function DumpAndDie($var, $dieMessage = \NULL) {
        if (is_null($dieMessage)) {
            $trace = debug_backtrace();
            $dieMessage = sprintf("Debugging interrupt in file \033[1m%s\033[0m  line \033[1m%s\033[0m\n", $trace[1]['file'], $trace[1]['line']);
        }
        self::Dump($var);
        echo $dieMessage;
        die();
    }

    /**
     * 
     * @param type $messageId
     * @param type $param1
     * @param type $param2
     */
    public static function Abort($messageId = \NULL, $param1 = \NULL, $param2 = \NULL) {
        if (isset($messageId)) {
            $errorMessage = self::Get($messageId, $param1, $param2);
            echo "$errorMessage\n";
        }
        else {
            echo $messageId;
        }
        die();
    }

    /**
     * 
     * @param type $messageId
     * @param type $param1
     * @param type $param2
     */
    public static function Display($messageId = \NULL, $param1 = \NULL, $param2 = \NULL) {
        echo self::Get($messageId, $param1, $param2) . "\n";
    }

    /**
     * 
     * @param type $messageId
     * @param type $param1
     * @param type $param2
     * @return type
     */
    public static function Get($messageId = \NULL, $param1 = \NULL, $param2 = \NULL) {
        $language = CLI\Analyser::GetLanguage();
        $format = '/CLI/Text/%s.php';
        if (count(self::$Error) == 0) {
            $msgFile = sprintf($format, 'Msg');
            CLI\FrontEnd::Loader($msgFile);
        }
        if (!isset(self::$Error[$language][$messageId])) {
            $text = $messageId;
        }
        else {
            $text = self::$Error[$language][$messageId];
        }
        if ($param1 != \NULL) {
            $text = sprintf($text, $param1, $param2);
        }
        return $text;
    }

} // end of class Messages

/**
 * A debugging tool using Debug static methods
 * 
 * @param mixed $var
 * @param boolean $die By default the function ends the program
 * @param type $Message An optional message
 */
function iris_debug($var, $die = TRUE, $Message = \NULL) {
    if ($die) {
        \Messages::DumpAndDie($var, $Message, 1);
    }
    else {
        \Messages::Dump($var);
    }
}

/**
 * 
 * @param type $message
 */
function echoLine($message) {
    verboseEchoLine($message, \TRUE);
}

/**
 * 
 * @param type $message
 * @param type $verbose
 */
function verboseEchoLine($message, $verbose) {
    if ($verbose) {
        echo $message . "\n";
    }
}

/**
 * A not very clean way to obtain a shell var value in Windows
 * 
 * @param type $var
 * @return type
 */
function winShellVar($var) {
    return str_replace("\n", "", shell_exec("echo %$var%"));
}
