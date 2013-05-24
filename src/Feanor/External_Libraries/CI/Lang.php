<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

/**
 * Language Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Language
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/language.html
 */
class CI_Lang
{
    /**
     * List of translations
     *
     * @var array
     */
    var $language = array();

    /**
     * List of loaded language files
     *
     * @var array
     */
    var $is_loaded = array();

    /**
     * Constructor
     *
     * @access	public
     */
    function __construct ()
    {
        log_message('debug', "Language Class Initialized");
    }

    // --------------------------------------------------------------------

    /**
     * Load a language file
     *
     * @access	public
     * @param	mixed	the name of the language file to be loaded. Can be an array
     * @param	string	the language (english, etc.)
     * @param	bool	return loaded array of translations
     * @param 	bool	add suffix to $langfile
     * @param 	string	alternative path to look for language file
     * @return	mixed
     */
    function load ($langfile = '', $idiom = '', $return = FALSE, $add_suffix = TRUE, $alt_path = '')
    {
        $langfile = str_replace('.php', '', $langfile);

        if ($add_suffix == TRUE) {
            $langfile = str_replace('_lang.', '', $langfile) . '_lang';
        }

        $langfile .= '.php';

        if (in_array($langfile, $this->is_loaded, TRUE)) {
            return;
        }

        $idiom = 'english';

        // Determine where the language file is and load it
        if (file_exists(BASEPATH . 'feanor/external/CI/language/' . $langfile)) {
            include(BASEPATH . 'feanor/external/CI/language/' . $langfile);
        }


        if (!isset($lang)) {
            log_message('error', 'Language file contains no data: language/' . $idiom . '/' . $langfile);
            return;
        }

        if ($return == TRUE) {
            return $lang;
        }

        $this->is_loaded[] = $langfile;
        $this->language = array_merge($this->language, $lang);
        unset($lang);

        log_message('debug', 'Language file loaded: language/' . $idiom . '/' . $langfile);
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Fetch a single line of text from the language array
     *
     * @access	public
     * @param	string	$line	the language line
     * @return	string
     */
    function line ($line = '')
    {
        $value = ($line == '' OR !isset($this->language[$line])) ? FALSE : $this->language[$line];

        // Because killer robots like unicorns!
        if ($value === FALSE) {
            log_message('error', 'Could not find the language line "' . $line . '"');
        }

        return $value;
    }

}
