<?php



namespace Iris\OS;

/*
 * This file is part of IRIS-PHP.
 *
 * IRIS-PHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * IRIS-PHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with IRIS-PHP.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * @copyright 2012 Jacques THOORENS
 */

/**
 * An abstract OS implementation. Allmost all methods are here.
 * Subclass Unix adds one more, Windows subclass adds various methods
 * depending on version.
 *
 * 
 * @author Jacques THOORENS (irisphp@thoorens.net)
 * @see http://irisphp.org
 * @license GPL version 3.0 (http://www.gnu.org/licenses/gpl.html)
 * @version $Id: $ */
abstract class _OS {// implements \Iris\Design\iSingleton 
    const MKDIR = 1;
    const COPY = 2;
    const UNLINK = 3;
    const RENAME = 4;
    const RMDIR = 5;
    const LINK = 6;
    const SYMLINK = 7;
    const TOUCH = 8;
    const PUT = 9;
    const GET = 10;

    public $tabLevel = 0;

    /**
     * An array containing all the format for displaying verbose messages
     * 
     * @var array(string)
     */
    protected $_format = array();

    /**
     *
     * @var Unix
     */
    protected static $_Instance = NULL;

    /**
     * return the instance of the active OS
     * @return Unix (or maybe Windows)
     */
    public static function GetOSInstance() {
        if (self::$_Instance == NULL) {
            if (self::_NeedWindows()) {
                self::$_Instance = new Windows();
            }
            else {
                self::$_Instance = new Unix();
            }
        }
        return self::$_Instance;
    }

    /**
     * TRUE if verbosity on
     * @var boolean  
     */
    protected $_verbose = FALSE;

    /**
     * a copy of verbosity state when simulation starts
     * @var boolean
     */
    protected $_oldVerbose = FALSE;

    /**
     * TRUE if simulation is on
     * @var boolean
     */
    protected $_simulate = FALSE;

    /**
     * The class behaves as a singleton (not marked by \Iris\Design\iSingleton
     * to minimize dependencies in CLI)
     */
    protected function __construct() {
        $this->_format[self::MKDIR] = "Creating directory %s\n";
        $this->_format[self::COPY] = "Copying %s to %s\n";
        $this->_format[self::UNLINK] = "Removing %s\n";
        $this->_format[self::RENAME] = "Moving/renaming %s to %s\n";
        $this->_format[self::RMDIR] = "Removing directory %s\n";
        $this->_format[self::LINK] = "Creating a link from %s to %s \n";
        $this->_format[self::SYMLINK] = "Creating a symbolic link for %s as %s\n";
        $this->_format[self::TOUCH] = "Creating file %s\n";
        $this->_format[self::PUT] = "Putting data in file %s\n";
        $this->_format[self::GET] = "Getting data from file %s\n";
    }

    /**
     * Gives everybody a complete access to a file
     * 
     * @param string $fileName
     */
     public function fullPermission($fileName){
         \chmod($fileName,0777);
     }
    
    /**
     * A substitute to standard mkdir with verbose and simulation modes
     * 
     * @param string $pathname the dir name
     * @param int $mode value of chmod for the file (default octal 755) 
     * @param boolean $recursive enable recursive creation of complexe path
     */
    public function mkDir($pathname, $mode = 0755, $recursive = \NULL) {
        $this->_verbose and $this->_echo(self::MKDIR, $pathname);
        $this->_simulate or mkdir($pathname, $mode, $recursive);
    }

    /**
     * A substitute to standard rmdir with verbose and simulation modes
     * 
     * @param string $dirname the dir name
     */
    public function rmDir($dirname) {
        $this->_verbose and $this->_echo(self::RMDIR, $dirname, $this->tabLevel);
        $this->_simulate or rmdir($dirname);
    }

    /**
     * A substitute to standard copy with verbose and simulation modes
     * 
     * @param string $source
     * @param string $dest
     */
    public function copy($source, $dest) {
        $this->_verbose and $this->_echo(self::COPY, array($source, $dest));
        $this->_simulate or copy($source, $dest);
    }

    /**
     * A substitute to standard symlink with verbose and simulation modes: 
     * creates a symbolic link
     * 
     * @param string $target
     * @param string $link 
     */
    public function symlink($target, $link) {
        $this->_verbose and $this->_echo(self::SYMLINK, array($target, $link));
        $this->_simulate or symlink($target, $link);
    }

    /**
     * A substitute to standard link with verbose and simulation modes
     * creates a hard link
     * @param string $from_path
     * @param string $to_path 
     */
    public function link($from_path, $to_path) {
        $this->_verbose and $this->_echo(self::LINK, array($from_path, $to_path));
        $this->_simulate or link($from_path, $to_path);
    }

    /**
     * A substitute to standard copy with verbose and simulation modes
     * 
     * @param string $oldname
     * @param string $newname
     */
    public function rename($oldname, $newname) {
        rename($oldname, $newname);
    }

    /**
     * A substitute to standard unlink with verbose and simulation modes
     * Delete a file
     * 
     * @param string $filename
     */
    public function unlink($filename) {
        $this->_verbose and $this->_echo(self::UNLINK, $filename, $this->tabLevel + 1);
        $this->_simulate or unlink($filename);
    }

    /**
     * A substitute to standard touch with verbose and simulation modes
     * Creates a new file
     * 
     * @param string $filename
     * @param int $time
     * @param int $atime 
     */
    public function touch($filename, $time=NULL, $atime=NULL) {
        $this->_verbose and $this->_echo(self::TOUCH, $filename);
        $this->_simulate or touch($filename, $time, $atime);
    }

    /**
     * A substitute to standard file_put_content with verbose and simulation modes
     * Fills a file with data
     * 
     * @param string $filename
     * @param mixed $data
     * @param int $flags
     */
    public function file_put_contents($filename, $data, $flags=NULL) {
        $this->_verbose and $this->_echo(self::PUT, $filename);
        $this->_simulate or file_put_contents($filename, $data, $flags);
    }

    public function file_get_contents($filename, $flags = \NULL) {
        $this->_verbose and $this->_echo(self::GET, $filename);
        if ($this->_simulate) {
            return "False data";
        }
        else {
            return file_get_contents($filename, $flags);
        }
    }

    /**
     * Creates a file using a template with some fields to replace. An array
     * with fields and matching values must be given in the form
     *     array(field1=>value1, field2=>value2...)
     * The replacement may occurs various time and the indexes and values are
     * treated as regular expressions
     * 
     * @param string $source the path to the original template file
     * @param string $destination the path to the new file
     * @param array $replacement an associative array with the fields and values 
     */
    public function createFromTemplate($source, $destination, $replacement=array()) {
        $text = $this->file_get_contents($source);
        foreach ($replacement as $from => $to) {
            $text = preg_replace("/$from/", "$to", $text);
        }
        $this->file_put_contents($destination, $text);
    }
    
    
    /**
     * This function has to determine if the running OS is Windows
     * 
     * @staticvar boolean $Answer
     * @return string 
     */
    protected static function _NeedWindows() {
        static $Answer = NULL;
        if (!is_null($Answer))
            return $Answer;
        $Anser = FALSE;
        ob_start();
        phpinfo(1);
        $s = ob_get_contents();
        ob_end_clean();
        $array = explode("\n", $s);
        $line = 0;
        $found = FALSE;
        do {
            if (strpos($array[$line], 'System =>') === 0) {
                $found = TRUE;
            }
            else
                $line++;
        }
        while (!$found);
        $lineOS = $array[$line];
        if (strpos(strtoupper($lineOS), 'WIN') !== FALSE)
            $Answer = TRUE;
        return $Anser;
    }

    /**
     * Activates the verbose mode of the operations
     * 
     * @param boolean $verbose 
     */
    public function setVerbose($verbose = TRUE) {
        $this->_verbose = $verbose;
        $this->_oldVerbose = $verbose;
    }

    /**
     * Activates the simulation of the operation (with verbosity)
     * 
     * @param boolean $simulate 
     */
    public function setSimulate($simulate = TRUE) {
        $this->_simulate = $simulate;
        if ($simulate) {
            $this->_oldVerbose = $this->_verbose;
            $this->_verbose = TRUE;
        }
        else {
            $this->_verbose = $this->_oldVerbose;
        }
    }

    /**
     * Display a verbose message of the current operation
     * @param int $messageType Number of the format to use
     * @param string/array $value  parameters (1 or 2)
     */
    protected function _echo($messageType, $values, $level=0) {
        for ($l = 0; $l < $level; $l++) {
            echo "  ";
        }
        //echo $level;
        if (is_string($values)) {
            $values = array($values);
        }
        $values[] = $values[] = '';
        echo $this->_(sprintf($this->_format[$messageType], $values[0], $values[1]));
    }

    /**
     * Future possible extension (translation)
     * @param string $message
     * @return string 
     */
    protected function _($message) {
        return $message;
    }

    public function modTabLevel($num) {
        $this->tabLevel += $num;
    }

    /**
     * Get user home directory 
     * 
     * @return string
     */
    public abstract function getUserHomeDirectory();
}


