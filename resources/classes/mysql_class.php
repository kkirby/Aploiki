<?php
/*
+----------------------------------------------------------+
| Aploiki is distributed under the CC-GNU GPL license.     |
| You may NOT remove or modify any 'powered by' or         |
| copyright lines in the code.                             |
| http://creativecommons.org/licenses/GPL/2.0/             |
+----------------------------------------------------------+
| Made by Kyle Kirby                                       |
+----------------------------------------------------------+
*/
$conn = mysql_connect($_AEYNIAS['config']['mysql']['server'], $_AEYNIAS['config']['mysql']['username'], $_AEYNIAS['config']['mysql']['password']) or die(mysql_error());
mysql_select_db($_AEYNIAS['config']['mysql']['database'], $conn);
class _mysql{
	//////////////////////////////
	// MySQL Class 2.0.1        //
	// Made by Kyle Kirby       //
	// Of Optimal Connection    //
	//////////////////////////////
	
	// sets the connection when making an instance of the class.
	function mysql(){
		$this->conn = $GLOBALS['conn'];
	}
	
	// Sets up all the infromation.
	private function set_parameters($param1 = null, $param2 = null, $param3 = null, $param4 = null){
		switch($this->method){
			case('SELECT'):
				$this->table = $param1 != null ? $param1 : $this->produce_error('You need to supply a table.');
				$this->selection = $param2 != null ? $param2 : $this->produce_error('You need to supply a selection.');
				$this->where = $param3;
				$this->extra = $param4;
			break;
			case('INSERT'):
				$this->table = $param1 != null ? $param1 : $this->produce_error('You need to supply a table.');
				if ($param2 == null)$this->produce_error('You need to supply your data.');
				else if (is_array($param2)){
					$this->data = $param2;
				}
				else $this->produce_error('Your data needs to be in array form: array(\'field1\'=>\'data1\',\'field2\'=>\'data2\')');
			break;
			case('UPDATE'):
				$this->table = $param1 != null ? $param1 : $this->produce_error('You need to supply a table.');
				$this->where = $param3 != null ? $param3 : $this->produce_error('You need to supply what to update.');
				if ($param2 == null)$this->produce_error('You need to supply your data.');
				else if (is_array($param2)){
					$this->data = $param2;
				}
				else $this->produce_error('Your data needs to be in array form: array(\'field1\'=>\'data1\',\'field2\'=>\'data2\')');
			break;
			case('DELETE'):
				$this->table = $param1 != null ? $param1 : $this->produce_error('You need to supply a table.');
				$this->where = $param2 != null ? $param2 : $this->produce_error('You need to supply what to delete.');
			break;
		}
	}
	
	// Put all the information together, for use in mysql_query
	private function put_it_together(){
		if (isset($this->extra) && $this->extra != null)$extra = ' ' . trim($this->extra);
		else $extra = '';
		switch($this->method){
			case('SELECT'):
				$this->build_the_where();
				$this->build_the_selection();
				$this->query = 'SELECT ' . $this->selection . ' FROM `' . $this->table . '` ' . $this->where . $extra;
			break;
			case('INSERT'):
				$this->build_the_data();
				list($key,$value) = $this->data;
				$this->query = 'INSERT INTO `'. $this->table . '` ('. $key . ') VALUES ('.$value.')';
			break;
			case('UPDATE'):
				$this->build_the_data();
				$this->build_the_where();
				$this->query = 'UPDATE `'.$this->table.'` SET '. $this->data . ' ' . $this->where;
			break;
			case('DELETE'):
				$this->build_the_where();
				$this->query = 'DELETE FROM `' . $this->table .'` ' . $this->where;
			break;
			
		}
	}
	
	// used for INSERT and UPDATE
	private function build_the_data(){
		switch($this->method){
			case('INSERT'):
				$i = 0;
				$inset_key = '';
				$inset_value = '';
				foreach ($this->data as $key => $value){
				    $value = get_magic_quotes_gpc() == 1 ?  stripslashes(mysql_real_escape_string($value)) : mysql_real_escape_string($value);
					$insert_key = ($i == 0) ? "`$key`" : $insert_key . ", `$key`";
					$insert_value = $i == 0 ? "'$value'" : $insert_value . ", '$value'";
					$i++;
				}
				$this->data = array($insert_key, $insert_value);
			break;
			case('UPDATE'):
				$i = 0;
				$return = '';
				foreach ($this->data as $key => $value){
				    $value = get_magic_quotes_gpc() == 1 ?  stripslashes(mysql_real_escape_string($value)) : mysql_real_escape_string($value);
					$return .= ($i == 0) ? "`$key` = '$value'" : ", `$key` = '$value'";
					$i++;
				}
				$this->data = $return;
			break;
		}
	}

	// Used for anything that has a WHERE clause
	private function build_the_where(){
		$where = $this->where;
		if ($where != null){
			// build the WHERE
			if (is_array($where)){
			    $i = 0;
				foreach($where as $key => $value){
				    $value = get_magic_quotes_gpc() == 1 ?  stripslashes(mysql_real_escape_string($value)) : mysql_real_escape_string($value);
					$where_clause = ($i == 0) ? "WHERE `$key` = '$value'" : $where_clause . " AND `$key` = '$value'";
					$i++;
				}
			}
			else if (is_string($where))$where_clause = $where;
			/////////////////
			$this->where = $where_clause;
		}
	}
	
	// Used if your selecting something
	private function build_the_selection(){
		// build the selection
		$selection = $this->selection;
		if ($selection != null){
			if (is_array($selection)){
				foreach($selection as $the_what)$selection_clause = (isset($all_what)) ? $the_what . ", `$the_what`" : "`$the_what`";
				$selection_count = count($selection);
			}
			else $selection_clause = $selection;
			
			$this->selection = $selection_clause;
		}
	}

	// Used to find out how much your selecting
	private function get_selection_count(){
		$selection_clause = $this->selection;
		if($selection_clause == '*')$selection_count = $this->get_field_count();
		else {
			$selection_clause_array = explode(',',$selection_clause);
			$selection_count = count($selection_clause_array);
		}
		return $selection_count;
	}
	
	// Works with get_selection_count if your selection is *
	private function get_field_count(){
		$result = mysql_query('SHOW COLUMNS FROM ' . $this->table,$this->conn) or $this->error = mysql_error();
		return mysql_num_rows($result);
	}
	
	// The actual update_row function, the other one is used to see if it needs to update
	// or insert the table.
	private function update_row_actual($table = null, $data = null, $where = null){
		$this->method = 'UPDATE';
		$this->set_parameters($table, $data, $where);
		$this->put_it_together();
		if (mysql_query($this->query,$this->conn))return true;
		else {
			$this->error = mysql_error();
			return false;
		}
	}
	
	// If theres an error, send it to this function.
	private function produce_error($error){
		echo $error;
		exit;
	}
	
	// Uneset all variblaes so the script doesn't get confused.
	private function restore(){
		unset($this->table);
		unset($this->selection);
		unset($this->where);
		unset($this->extra);
		unset($this->data);
		unset($this->method);
		mysql_close($this->conn);
	}
	/// Begin normal functions ///
	
	// Quickly grab information, will automatically turn data into an ARRAY if needs to.
	function quick_grab($table = null, $selection = '*', $where = null,$extra = null, $multidimensional_array = 'auto'){
		$this->method = 'SELECT';
		$this->set_parameters($table, $selection, $where, $extra);
		$this->put_it_together();
		////////////
		if ($mysql_query = mysql_query($this->query, $this->conn)){
			$row_count = mysql_num_rows($mysql_query);
			if ($multidimensional_array == 'true' || $multidimensional_array === true){
			    if($row_count == 1 && $this->get_selection_count() == 1)
			        $result[] = mysql_result($mysql_query,0,0);
			    else if($row_count > 1){
			        if($this->get_selection_count() == 1)
			            while ($data = mysql_fetch_array($mysql_query,MYSQL_ASSOC))$result[] = current(array_values($data));
			        else while ($data = mysql_fetch_array($mysql_query,MYSQL_ASSOC))$result[] = $data;
			    }
			    else return false;
			}
			else if($multidimensional_array == 'auto'){
				if($row_count > 1){
				    if ($this->get_selection_count() > 1){
				        while ($data = mysql_fetch_array($mysql_query,MYSQL_ASSOC))$result[] = $data;
    					if (count($result)  == 1)$result = $result[0];
				    }
				    else {
				        for($i=0;$i<$row_count;$i++)$result[] = mysql_result($mysql_query,$i,0);
				    }
				}
				else if ($row_count == 1){
					if ($this->get_selection_count() > 1){
						if($this->get_selection_count() == 1)
    			            while ($data = mysql_fetch_array($mysql_query,MYSQL_ASSOC))$result[] = current(array_values($data));
    			        else while ($data = mysql_fetch_array($mysql_query,MYSQL_ASSOC))$result[] = $data;
					}
					else {
						$result = mysql_result($mysql_query,0,0);
					}
				}
				else if ($row_count == 0)$result = false;	
			}
			else if($multidimensional_array == 'false' || $multidimensional_array === false){
			    if ($row_count >= 1)$result = mysql_result($mysql_query,0,0);
			    else $result = false;
			}
			return $result;
		}
		else {
			$this->error = mysql_error();
			return false;
		}
		$this->restore();
		////////////
		
	}
	
	function query($query, $multidimensional_array = 'auto'){
        $this->query = $query;
		////////////
		if ($mysql_query = mysql_query($this->query, $this->conn)){
			$row_count = mysql_num_rows($mysql_query);
			$fieldCount = mysql_num_fields($mysql_query);
			if ($multidimensional_array == 'true' || $multidimensional_array === true){
			    if ($row_count > 0){
			        while ($data = mysql_fetch_array($mysql_query,MYSQL_ASSOC))$result[] = $data;
			    }
			    else return false;
			}
			else if($multidimensional_array == 'auto'){
				if($row_count > 1){
				    if ($fieldCount > 1){
				        while ($data = mysql_fetch_array($mysql_query,MYSQL_ASSOC))$result[] = $data;
    					if (count($result)  == 1)$result = $result[0];
				    }
				    else {
				        for($i=0;$i<$row_count;$i++)$result[] = mysql_result($mysql_query,$i,0);
				    }
				}
				else if ($row_count == 1){
					if ($fieldCount > 1){
						while ($data = mysql_fetch_array($mysql_query,MYSQL_ASSOC))$result = $data;
					}
					else {
						$result = mysql_result($mysql_query,0,0);
					}
				}
				else if ($row_count == 0)$result = false;	
			}
			else if($multidimensional_array == 'false' || $multidimensional_array === false){
			    if ($row_count >= 1)$result = mysql_result($mysql_query,0,0);
			    else $result = false;
			}
			return $result;
		}
		else {
			$this->error = mysql_error();
			return false;
		}
		$this->restore();
		////////////
		
	}

	// Just validates if a table exists.
	function validate($table = null, $where = null){
		$this->method = 'SELECT';
		$this->set_parameters($table, '*', $where, null);
		$this->put_it_together();
		if ($mysql_query = mysql_query($this->query, $this->conn))return mysql_num_rows($mysql_query) == 1 ? true : false;
		else {
			$this->error = mysql_error();
			return false;
		}	
		$this->restore();
	}
	
	// Grabs the row count
	function get_row_count($table = null, $where = null, $extra = null){
		$this->method = 'SELECT';
		$this->set_parameters($table, '*', $where, $extra);
		$this->put_it_together();
		if ($mysql_query = mysql_query($this->query, $this->conn))return mysql_num_rows($mysql_query);
		else {
			$this->error = mysql_error();
			return false;
		}
		$this->restore();
		
	}
	
	// Insert a new row to the selected table.
	function insert_row($table = null, $data = null){
	    if (isset($data[0][0])){
	        foreach ($data as $row)$this->insert_row($table,$row);
	    }
	    else {
    		$this->method = 'INSERT';
    		$this->set_parameters($table, $data);
    		$this->put_it_together();
    		if (mysql_query($this->query,$this->conn))return mysql_insert_id($this->conn);
    		else {
    			$this->error = mysql_error();
    			return false;
    		}
    		$this->restore();
    	}
	}

	// Updates a row, will automatically add the row if it cannot find the row to update.
	function update_row($table = null, $data = null, $where = null, $auto_add = true){
	    $validation = $this->validate($table, $where);
		if ($validation)return $this->update_row_actual($table,$data,$where);
		else if (!$validation && $auto_add == true)return $this->insert_row($table, $data);
		else return false;
	}

	function delete_row($table = null, $where = null){
		$this->method = 'DELETE';
		$this->set_parameters($table, $where);
		$this->put_it_together();
		if (mysql_query($this->query,$this->conn))return true;
		else {
			$this->error = mysql_error();
			return false;
		}
		$this->restore;
	}
}
?>