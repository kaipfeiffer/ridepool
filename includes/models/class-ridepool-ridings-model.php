<?php
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

require_once KPM_COUNTER_PLUGIN_PATH . 'includes/abstracts/abstract-kpm-counter-model-ctagged.php';

// class Kpm_Counter_Readings_Model extends Kpm_Counter_Model_Filtered
class Ridepool_Ridings_Model extends Model_Ctagged_Abstract
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
     * $columns
     * 
     * @var array
     * Assoziatives Array mit den Spaltennamen und den zugehörigen printf-Platzhaltern
     */
    protected static $columns = [
        'id'            => '%d',
        'ctag'          => '%d',
        'counter_id'    => '%d',
        'reading'       => '%f',
        'consumption'   => '%f',
        'temperature'   => '%f',
        'reading_date'          => '%s',
        'remark'        => '%s',
    ];


    /**
     * $import_file
     * the name of the file to import
     * 
     * @var string
     */
    protected static $import_file = 'readings.csv';


    /**
     * $primary
     * the name of the primary index
     * 
     * @var string
     */
    protected static $primary = 'id';


    /**
     * $table_name
     * the name of the table without wp-prefix
     * 
     * @var string
     */
    protected static $auth_name = 'counters';


    /**
     * $table_name
     * the name of the table without wp-prefix
     * 
     * @var string
     */
    protected static $table_name = 'readings';


    /**
     * $user_column
     * 
     * die Spalte in der Haupttabelle, die die User-Informationen beinhaltet
     * 
     * @var integer
     */
    protected static $user_column = 'counter_id';


    /**
     * $user_primary
     * the name of the primary index of the user-table
     * 
     * @var string
     */
    protected static $user_primary = 'id';


    /**
     * $user_table_key
     * 
     * the key field which is identifies the user in the user-table
     * 
     * @var string
     */
    protected static $user_table_key = 'counter_owner';


    /**
     * $user_table_name
     * the name of the table with the user-id without wp-prefix
     * 
     * @var string
     */
    protected static $user_table_name = 'counter';


    /**
     * PRIVATE METHODS
     */

    /**
     * @function  create_table
     * 
     * creates the table
     */
    protected static function create_table()
    {
        global $wpdb;

        $sql = sprintf(
            'CREATE TABLE %1$s (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                counter_id bigint(20) NOT NULL,
                reading float(11,4) NOT NULL,
                consumption float(11,4) NOT NULL,
                temperature float(11,4) NOT NULL,
                reading_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                ctag bigint(20) unsigned DEFAULT "1",
                remark varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                PRIMARY KEY (id),
                KEY counter_id (counter_id,reading_date)
            ) ENGINE=InnoDB %2$s;',
            static::get_tablename(),
            $wpdb->get_charset_collate()
        );

        dbDelta($sql);
    }
}
