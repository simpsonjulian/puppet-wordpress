<?php

class XinitBackupDatabaseHelper
{
	var $_tableName;
	
	var $_connection;
	
	var $_prefix;
	
	function XinitBackupDatabaseHelper()
	{
		global $wpdb;
		$this->_connection = $wpdb;
		$this->_prefix = $wpdb->prefix;
		$this->_tableName = $wpdb->prefix . 'beifen';
	}

	function deleteBackupEntry($id)
	{
		$sql = "DELETE FROM $this->_tableName where id = $id;";
		$this->_connection->query($sql);
	}

	function existingBackup($bkp_name)
	{
		$sql = "SELECT COUNT(name) FROM $this->_tableName WHERE name='$bkp_name'";
		$result = $this->_connection->get_var($sql);
		if(0<(int)$result)
		{
			return true;
		}
		{
			return false;
		}
	}

	function getMysqlDump()
	{
		mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);
		$tables = mysql_list_tables(DB_NAME);
		while ($td = mysql_fetch_array($tables))
		{
			$table = $td[0];	
			if($table == $this->_tableName)
				continue;
			$SQL[] .= "DROP TABLE IF EXISTS `$table`;";			
			$r = mysql_query("SHOW CREATE TABLE `$table`");
			if ($r)
			{
				$insert_sql = "";
				$d = mysql_fetch_array($r);
				$d[1] .= ";";
				$SQL[] = $d[1];
				$table_query = mysql_query("SELECT * FROM `$table`");
				$num_fields = mysql_num_fields($table_query);
				while ($fetch_row = mysql_fetch_array($table_query))
				{
					$insert_sql .= "INSERT INTO $table VALUES(";
					for ($n=1;$n<=$num_fields;$n++)
					{
						$m = $n - 1;
						$insert_sql .= "\"".mysql_real_escape_string($fetch_row[$m])."\", ";
						$insert_sql = str_replace("'","\'",$insert_sql);
					}
					$insert_sql = substr($insert_sql,0,-2);
					$insert_sql .= ");\n";
				}
				
				if ($insert_sql!= "")
				{
					$SQL[] = $insert_sql;
				}
			}
		}
		return implode("\r", $SQL);
	}

	function getNameByID($id)
	{
		$sql = "SELECT name FROM $this->_tableName WHERE id='$id'";
		return $this->_connection->get_var($sql);
	}

	function getIDByName($name)
	{
		$sql = "SELECT id FROM $this->_tableName WHERE name='$name'";
		return $this->_connection->get_var($sql);
	}
	
	function getBackupByID($id)
	{
		$sql = "SELECT name, location,DATE_FORMAT(created,'%Y/%m/%d at %H:%i') as created, type, zipped FROM $this->_tableName WHERE id='$id'";
		return $this->_connection->get_row($sql, OBJECT);
	}

	function getBackupCount()
	{
		$sql = "SELECT count(id) FROM $this->_tableName";
		return $this->_connection->get_var($sql);
	}
	
	function getTableDump($table)
	{
		if($this->_tableName!=$table && substr($table,0,strlen($this->_prefix))==$this->_prefix)
		{
			mysql_query("LOCK TABLE $table WRITE") or die("DUMP_TABLE: Could not lock table $table - ".mysql_error());

			$temp = mysql_fetch_assoc(mysql_query("SHOW CREATE TABLE $table")) or die("DUMP_TABLE: SHOW CREATE TABLE $table failed - ".mysql_error());
			$dump = "DROP TABLE IF EXISTS `$table`-- End of query\n";
			$dump .= $temp['Create Table']."-- End of query\n";
			
			$result = mysql_query("SELECT * FROM $table") or die("DUMP_TABLE: SELECT * FROM $table failed - ".mysql_error());

			if(mysql_num_rows($result)>0)
			{
				while ($row = mysql_fetch_assoc($result)) {
					$fields = "(";
					$values = "(";

					foreach ($row as $key => $value) {
						$fields .= mysql_escape_string($key).", ";
						$values .= "'".mysql_escape_string($value)."', "; // what about NULL ?
					}

					$fields = substr($fields, 0, -2).")";
					$values = substr($values, 0, -2).")";

					$dump .= "INSERT INTO $table $fields VALUES $values-- End of query\n";
				}
			}
			mysql_query("UNLOCK TABLES") or die("DUMP_TABLE: Could not unlock table $table - ".mysql_error());
			
			return $dump;
		}
		else
		{
			return '';
		}
	}

	function getTableNames()
	{
		$table_names = array();
		$tables = mysql_query("SHOW TABLES");
		while ($table = mysql_fetch_array($tables))
		{
			$table_names[] = $table[0];
		}
		return $table_names;
	}
	
	function insertNewBackupEntry($name, $location, $type, $zipped)
	{
		$sql = "INSERT INTO $this->_tableName (name, location, type, zipped) VALUES('$name','" . mysql_real_escape_string($location) . "','$type',$zipped);";
		$this->_connection->query($sql);
	}
	
	function restoreDumpFromString($dump)
	{
		// clean dump string
		if(mysql_connect(DB_HOST,DB_USER,DB_PASSWORD))
		{
			if(mysql_select_db(DB_NAME))
			{
				$queries = explode(';', $dump);
				unset($dump);
				foreach($queries as $query)
				{
					if(!mysql_query($query))
						echo mysql_error() .'<br/>';
				}
			}
			else
			{
				echo mysql_error();
			}
		}
		else
		{
			echo mysql_error();
		}
	}
}
?>