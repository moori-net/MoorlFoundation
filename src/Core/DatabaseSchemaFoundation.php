<?php

namespace MoorlFoundation\Core;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Uuid\Uuid;

class DatabaseSchemaFoundation
{
    /**
     * @var DefinitionInstanceRegistry
     */
    private $definitionInstanceRegistry;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var array
     */
    private $log;

    /**
     * @var array
     */
    private $schema;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Array
     */
    private $languageIds;

    public function __construct(
        DefinitionInstanceRegistry $definitionInstanceRegistry,
        Connection $connection
    )
    {
        // TODO: Entfernen
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
        $this->connection = $connection;
    }

    /**
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @param Context $context
     */
    public function setContext(Context $context): void
    {
        $this->context = $context;
    }

    public function getEngine()
    {
        return "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }

    public function addLog(string $var): void
    {
        $this->log[] = $var;
    }

    public function setSchema(Plugin $plugin)
    {
        $dir = $plugin->getMigrationPath() . "/schema";

        foreach (glob($dir . "*.json") as $filename) {
            $schemaName = basename($filename);

            $this->schema[$schemaName] = json_decode(file_get_contents($filename), true);
        }
    }

    public function syncSchema()
    {

        $log = "Start...\n";

        if (!is_array($this->data['customer_slug_dst'])) {

            $this->data['customer_slug_dst'] = [$this->data['customer_slug_dst']];

        }

        foreach ($this->data['customer_slug_dst'] as $customerSlug) {

            $log .= self::updateSchema($customerSlug, $this->data);

        }

        $log .= "Finish...\n";

        return Helpers::ajaxResponse('Update Report', true, "<textarea class='form-control' readonly>" . $log . "</textarea>", [], null);

    }

    static function updateSchema($customerSlug, $data)
    {

        $log = "Customer: " . $customerSlug . "\n";

        $erwingoDB = ErwingoDB::getInstance();

        $customerDB = self::getDatabase($customerSlug);

        $baseModules = $erwingoDB->select("module", "module_id", []);

        $log .= "Parent Modules: " . implode(", ",$baseModules) . "\n";

        $customerModules = $customerDB->select("module", "module_id", []);

        $log .= "Customer Modules: " . implode(", ",$customerModules) . "\n";

        $customerModules = array_unique(array_merge($baseModules, $customerModules));

        $log .= "Merged Modules: " . implode(", ",$customerModules) . "\n";

        //$oldSchema = self::getSchema($customerDB);

        $newSchema = self::getNewSchema($customerModules);

        foreach ($newSchema as $table => $cols) {

            $keys = "";
            $sql = 'CREATE TABLE IF NOT EXISTS `' . $table . '` (';

            foreach ($cols as $c) {

                if (isset($c['Key']) && $c['Key'] != "") {
                    if ($c['Key'] == "PRI") {
                        $keys .= " PRIMARY KEY  (`" . $c['Field'] . "`) ";
                    } else if ($c['Key'] == "UNI") {
                        if ($keys != "") {
                            $keys .= ", ";
                        }
                        $keys .= " UNIQUE (`" . $c['Field'] . "`) ";
                    } else if ($c['Key'] == "MUL") {
                        if ($keys != "") {
                            $keys .= ", ";
                        }
                        $keys .= " INDEX (`" . $c['Field'] . "`) ";
                    }
                }
                $null = " NOT NULL ";
                if (isset($c['Null'])) {
                    if ($c['Null'] == "YES") {
                        $null = " NULL ";
                    } else if ($c['Null'] == "NO") {
                        $null = " NOT NULL ";
                    }
                }
                $default = "";
                if (isset($c['Default']) && $c['Default'] != NULL) {
                    if ($c['Default'] == 'CURRENT_TIMESTAMP') {
                        $default = " default " . $c['Default'] . " ";
                    } else {
                        $default = " default '" . $c['Default'] . "' ";
                    }

                }
                $extra = "";
                if (isset($c['Extra'])) {
                    $extra = $c['Extra'];
                }
                $sql .= "`" . $c['Field'] . "` " . $c['Type'] . $null . $default . $extra . ", ";

            }

            $sql .= $keys;

            $sql = rtrim($sql, ", ") . " );";

            $result = $customerDB->query("SHOW TABLES LIKE  '" . $table . "'");

            if ($result->rowCount() == 0) {

                if (!$customerDB->query($sql)) {
                    echo "\n" . $sql;
                    exit;
                } else {
                    //$log .= print_r($newSchema[$table],true);
                    $log .= "Table Created : " . $table . "\n";
                }
            }

        }

        foreach ($newSchema as $table => $cols) {

            $res = $customerDB->query('DESCRIBE `' . $table . '`;');

            if ($data['collate'] == "utf8_general_ci") {
                $utf8 = "SET foreign_key_checks = 0; ALTER TABLE `" . $table . "` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci; SET foreign_key_checks = 1; ";
                $customerDB->query($utf8);
            }

            $struct = array();

            while ($struct[] = $res->fetch(\PDO::FETCH_ASSOC)) ;

            array_pop($struct);

            $struct = self::assoc_prime($struct, array("key" => "Field"), true);

            $cols = self::assoc_prime($cols, array("key" => "Field"), true);

            $fc = array_diff(array_keys($cols), array_keys($struct));
            if (count($fc) > 0) {
                foreach ($fc as $f) {
                    $new_field[$table][$f] = $cols[$f];
                }
            }

            $fd = array_diff(array_keys($struct), array_keys($cols));
            if (count($fd) > 0) {
                foreach ($fd as $f) {
                    $delete_field[$table][$f] = $struct[$f];
                }
            }

            foreach ($struct as $st => $s) {

                foreach ($cols as $co => $c) {

                    if ($st == $co) {

                        $change = array_merge(array_diff($c, $s), array_diff($s, $c));

                        unset($change['Key']); // Primary Key übernahme geht iwie net

                        if (count($change) > 0) {

                            $alter[$table][$co] = $c;

                        }

                    }

                }

            }

        }

        $log .= self::alter_table($customerDB, $alter, "CHANGE");

        $log .= self::alter_table($customerDB, $new_field, "ADD");

        if ($data['drop_tables'] == '1') {

            $log .= self::alter_table($customerDB, $delete_field, "DROP");

        }

        $erwingoDB->update('_customer', ['customer_last_sync' => date("Y-m-d H:i:s")], ['customer_slug' => $customerSlug]);

        //$log .= self::alter_table($customerDB,$delete_field,"DELETE");

        return $log;

    }

    static function assoc_prime($array, $keyval, $multi = FALSE)
    {
        $output = array();
        if ($multi) {
            for ($i = 0; $i < count($array); $i++) {
                $output[$array[$i][$keyval['key']]] = $array[$i];
            }
        } else {
            for ($i = 0; $i < count($array); $i++) {
                $output[$array[$i][$keyval['key']]] = $array[$i][$keyval['value']];
            }
        }
        return $output;
    }

    static function alter_table($db, $tables, $type = "CHANGE")
    {

        $log = "";

        if (count($tables) <= 0) {
            return;
        }

        foreach ($tables as $tab => $col) {

            $sql = " ALTER TABLE `$tab`";
            foreach ($col as $c) {
                $keys = "";

                if (true == false) {
                    if (isset($c['Key']) && $c['Key'] != "") {
                        if ($c['Key'] == "PRI") {
                            $keys .= " PRIMARY KEY  (`" . $c['Field'] . "`) ";
                        } else if ($c['Key'] == "UNI") {
                            if ($keys != "") {
                                $keys .= ", ";
                            }
                            $keys .= ",ADD UNIQUE (`" . $c['Field'] . "`) ";
                        } else if ($c['Key'] == "MUL") {
                            /*if($keys!=""){
                                $keys.=", ";
                            }*/
                            $keys .= ", ADD INDEX (`" . $c['Field'] . "`) ";
                        }
                    }
                }

                //if null remove index
                if (isset($c['Key']) && $c['Key'] == "") {
                    //$keys=", DROP INDEX `".$c['Field']."`";
                }

                $null = " NOT NULL ";
                if (isset($c['Null'])) {
                    if ($c['Null'] == "YES") {
                        $null = " NULL ";
                    } else if ($c['Null'] == "NO") {
                        $null = " NOT NULL ";
                    }
                }

                $default = "";
                if (isset($c['Default']) && $c['Default'] != NULL) {
                    if ($c['Default'] == 'CURRENT_TIMESTAMP') {
                        $default = " default " . $c['Default'] . " ";
                    } else {
                        $default = " default '" . $c['Default'] . "' ";
                    }
                }

                $extra = "";
                if (isset($c['Extra'])) {
                    $extra = $c['Extra'];
                }

                $rename = "";
                if ($type == "CHANGE") {

                    $rename = $c['Field'];
                    if (isset($c['Rename']) && $c['Rename'] != "") {
                        $rename = $c['Rename'];
                    }
                    $sql .= " CHANGE `" . $c['Field'] . "` `$rename` " . $c['Type'] . $null . $default . $extra . " ";
                    $log .= "Table : $tab , Change Field : " . $c['Field'] . "\n";

                } elseif ($type == "DROP") {

                    $sql .= " DROP `" . $c['Field'] . "` ";
                    $log .= "Table : $tab , Drop Field : " . $c['Field'] . "\n";

                } else {

                    $sql .= " $type `" . $c['Field'] . "` $rename " . $c['Type'] . $null . $default . $extra . " ";
                    $log .= "Table : $tab , Added Field : " . $c['Field'] . "\n";
                    //$log .= $sql . "\n\n";

                }

                $sql .= $keys;
                if (end($col) != $c) {
                    $sql .= ", ";
                }

            }
            $sql .= " ;";

            $log .= "\n" . $sql . "\n\n";

            $db->query($sql);

        }

        return $log;

    }


    public static function getSchema($sourceDB)
    {

        $rows = array();

        $result = $sourceDB->query("SHOW FULL TABLES WHERE Table_type != 'VIEW';");

        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {

            if ($row[key($row)] != "wata") {

                $rows[] = $row[key($row)];

            }

        }

        $schema = array();

        foreach ($rows as $v) {
            $res = $sourceDB->query('DESCRIBE `' . $v . '`;');
            $array = array();
            while ($srow = $res->fetch(\PDO::FETCH_ASSOC))
                $array[] = $srow;
            $schema[$v] = $array;
        }

        return $schema;

    }



    /**
     * Save structure dump to {customer}/modules/admin-tools/files/mysql/customer_schema.sql
     * @param type $host
     * @param type $user
     * @param type $password
     * @param type $database
     * @return type
     */
    public function dumpCustomerSchema()
    {

        $erwingoDB = ErwingoDB::getInstance();
        $customer = $erwingoDB->get('_customer', '*', ['customer_slug' => $this->data['customer_slug_src']]);

        $crypt = new Encryption();

        $this->dumpCustomerStructureTemplate(
            $customer['customer_db_server'],
            $customer['customer_db_username'],
            $crypt->decrypt($customer['customer_db_password']),
            $customer['customer_db_name']
        );

        return Helpers::ajaxResponse("Bitte Erfolg prüfen", true, "Die Daten sollten gespeichert worden sein.");

    }

    /**
     * Save structure dump to {customer}/modules/admin-tools/files/mysql/customer_schema.sql
     * @param type $host
     * @param type $user
     * @param type $password
     * @param type $database
     * @return type
     */
    public function dumpCustomerStructureTemplate($host, $user, $password, $database)
    {

        $customer = Customer::getInstance();
        $dumpdir = $customer->getMasterDirectory("modules/admin-tools/files/mysql") ?: $customer->getDirectory("modules/admin-tools/files/mysql");

        if (!is_dir($dumpdir)) {
            mkdir($dumpdir, 0777, true);
        }

        $dumpfile = addslashes($dumpdir . "customer_schema.sql");

        exec("mysqldump -d -h $host -u $user -p$password $database > \"$dumpfile\"", $output, $request_var);

        return $request_var;

    }

    /**
     * Save customer options
     * @param type $host
     * @param type $user
     * @param type $password
     * @param type $database
     * @return type
     */
    public function dumpCustomerOptions($customer)
    {

        $customerDB = new DB(array_merge(Config::get("database"), $customer));

        // TODO
    }
}