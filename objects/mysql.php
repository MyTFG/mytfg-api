<?php
namespace Mytfg\Objects;

class Mysql {
    private static $db;

    public static function init($server, $db, $user, $password) {
        Mysql::$db = new \PDO('mysql:host='.$server.';dbname=' . $db, $user, $password, array(
            \PDO::ATTR_PERSISTENT => true
        ));
    }

    public static function exec($query) {
        global $res;
        $res->db_call($query);
        $r = Mysql::$db->exec($query);
        if ($r === false) {
            $res->log(Mysql::$db->errorInfo());
        }
        return $r;
    }

    public static function insertId() {
        return Mysql::$db->lastInsertId();
    }

    public static function query($query, $join = false) {
        global $res;
        $query = trim($query);
        if ($join != false) {
            $query = rtrim($query, ";");
            $join_query = " T LEFT JOIN objects O ON O.localId = T.id AND O.type LIKE '%".$join."'";
            $query = "SELECT T.*, O.id as gid FROM (". $query . ")" . $join_query;
        }
        $res->db_call($query);
        $r = Mysql::$db->query($query);
        if ($r === false) {
            $res->log(Mysql::$db->errorInfo());
            $res->send(500);
        }
        return $r;
    }

    /*
     * PRESETS: QUERY BUILDER
     */
    public static function insert($table, $map) {
        $query = "INSERT INTO `".$table."` (";
        $keys = array();
        $values = array();
        foreach ($map as $key => $val) {
            $keys[] = "`" . $key . "`";
            $values[] = "" . Mysql::esc($val) . "";
        }
        $query .= implode(", ", $keys);
        $query .= ") VALUES (";
        $query .= implode(", ", $values);
        $query .= ");";
        return $query;
    }

    public static function update($table, $map, $id) {
        $query = "UPDATE `" . $table. "` SET ";
        $assignments = array();
        foreach ($map as $key => $val) {
            $assignments[] = "`" . $key . "` = " . Mysql::esc($val) . "";
        }
        $query .= implode(", ", $assignments);
        $query .= " WHERE `id` = " . Mysql::esc($id) . ";";
        return $query;
    }

    public static function delete($table, $id) {
        return "DELETE FROM `" . $table . "` WHERE `id` = " . Mysql::esc($id) . ";";
    }

    public static function load($table, $id) {
        return "SELECT * FROM `" . $table . "` WHERE `id` = " . Mysql::esc($id) . ";";
    }

    public static function select($table, $whereMap, $fields = true) {
        if (is_array($fields)) {
            $field = implode(", ", $fields);
        } else {
            $field = "*";
        }

        if (is_array($whereMap)) {
            $wheres = array();
            foreach ($whereMap as $key => $value) {
                $wheres[] = "`" . $key . "` = " . Mysql::esc($value) . "";
            }
            $where = implode(" AND ", $wheres);
        } else {
            $where = "1";
        }

        $query = "SELECT " . $field . " FROM " . $table . " WHERE " . $where . ";";
        return $query;
    }

    public static function esc($string) {
        return Mysql::$db->quote($string);
    }

    public static function t2m($time) {
        return date("Y-m-d H:i:s", $time);
    }

    public static function m2t($str) {
        return strtotime($str);
    }
}
?>
