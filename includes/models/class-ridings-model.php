<?php
namespace Loworx\Ridepool;

if (!defined('WPINC')) {
    die;
}

/**
 * Class for all ridings
 *
 * @author  Kai Pfeiffer <kp@loworx.com>
 * @package ridepool
 * @since   1.0.0 
 */

// require_once KPM_COUNTER_PLUGIN_PATH . 'includes/abstracts/abstract-kpm-counter-model-filtered.php';

// require_once KPM_COUNTER_PLUGIN_PATH . 'includes/abstracts/abstract-kpm-counter-model-ctagged.php';

// class Kpm_Counter_Readings_Model extends Kpm_Counter_Model_Filtered
class Ridings_Model
{
    /**
     * VARIABLES
     */

    /**
     * @var string class_name
     * 
     * diese Variable muss in der abgeleiteten Klasse mit dem Inhalte der Konstanten __CLASS__
     * belegt werden, damit die Instanzen richtig funktionieren
     */
    protected static $class_name = __CLASS__;


    /**
     * PRIVATE METHODS
     */


    /**
     * PUBLIC METHODS
     */

    /**
     * @function  create_table
     * 
     * creates the table
     * 
     * @param   array
     * @return  int
     */
    public static function create_table($data):int
    {
        error_log(__CLASS__.'->'.__LINE__.'->'.print_r($data,1));
        return 0;
    }
}
