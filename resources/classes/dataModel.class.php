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

if ( false === function_exists('lcfirst') ): 
    function lcfirst( $str ) 
    { return (string)(strtolower(substr($str,0,1)).substr($str,1));} 
endif;

class mySQL{
    function __construct($query){
        global $conn;
        $this->mySQLConnection = $conn;
        $this->query = null;
        $this->queryResult = null;
        $this->setQuery($query);
        $this->queryExecuted = false;
    }
    
    function checkExecution($error = true){
        if($this->queryExecuted == false && isset($this->query))
            if(($return = $this->executeQuery()) !== true)die($return);
        if($error==true && $this->queryExecuted == false)die('You cannot run a function without setting a query first and executing it.');
        return $this->queryExecuted;
    }
    
    function setQuery($query){
        if(get_parent_class($query) != 'mySQLQuery')user_error('Property one of setQuery must be a mySQLQuery object in object mySQL');
        else $this->query = $query;
    }
    
    public function executeQuery(){
        if($this->queryResult = mysql_query($this->query->output(),$this->mySQLConnection)){
            $this->queryExecuted = true;
            return true;
        }
        else return mysql_error();
    }
    
    function getColumnCount(){
        $this->checkExecution();
        return $this->query->getColumnCount();
    }
    
    public function insertID(){
        $this->checkExecution();
        $id = mysql_insert_id($this->mySQLConnection);
        if($id == 0)return false;
        else return $id;
    }
    
    public function rowCount(){
        $this->checkExecution();
        return mysql_num_rows($this->queryResult);
    }
    
    public function doesExist(){
        $this->checkExecution();
        return $this->rowCount() == 1 ? true : false;
    }
    
    public function fetchArray($multidimensional = 'auto',$resultType = MYSQL_BOTH){
        $this->checkExecution();
        $return = null;
        if($multidimensional === 'auto'){
            if($this->rowCount() == 1){
                if($this->getColumnCount() == 1)return mysql_result($this->queryResult,0,0);
                else return mysql_fetch_array($this->queryResult,$resultType);
            }
            else if ($this->rowCount() > 1){
                if($this->getColumnCount() == 1)
                    while($array = mysql_fetch_array($this->queryResult,$resultType))$return[] = $array[0];
                else{
                    while($array = mysql_fetch_array($this->queryResult,$resultType))$return[] = $array;
                }
                return $return;
            }
            else echo 'No fields found.';
        }
        else if ($multidimensional === true){
            while($array = mysql_fetch_array($this->queryResult,$resultType))
                $return[] = $array;
            return $return;
        }
        else if ($multidimensional === false){
            return mysql_result($this->queryResult,0,0);
        }
    }
    
    function __destruct(){
        
    } 
}

class mySQLQuery{
    function __construct(){
        $this->mySQLTable = null;
        $this->mySQLWhere = null;
        $this->mySQLQuery = null;
    }
    function setTable($table){
        if(get_class($table) == 'mySQLTable')$this->mySQLTable = $table;
        else if(is_string($table))$this->mySQLTable = new mySQLTable($table);
        else user_error('Property one of setTable must either be a string or a mySQLTable object.');
        return $this;
    }
    function getWhere(){
        $where = '';
        if(isset($this->mySQLWhere)){
            $where = ' '. $this->mySQLWhere->output();
        }
        return $where;
    }
    function setWhere($where){
        if(get_class($where) == 'mySQLWhereClause'){
            $this->mySQLWhere = $where;
            $this->mySQLWhere->mySQLQueryClass = $this;
        }
        else if(get_class($where) == 'mySQLClause'){
            $newWhere = new mySQLWhereClause;
            $newWhere->addClause($where);
            $this->mySQLWhere=$newWhere;
            $this->mySQLWhere->mySQLQueryClass = $this;
        }
        else user_error('Property one of setWhere must be a mySQLWhereClause object.');
        return $this;
    }
}

class mySQLQueryInsert extends mySQLQuery{
    function __construct(){
        $this->mySQLTable = null;
        $this->mySQLData = null;
        $this->mySQLQuery = null;
    }
    
    function getColumnCount(){
        return count($this->mySQLData->getData());
    }
    
    function setData($data){
        if(get_class($data)=='mySQLQueryData'){
            $this->mySQLData = $data->setTypeToInsert();
            $this->mySQLData->mySQLQueryClass = $this;
        }
        else user_error('Property one of setData must be a mySQLQueryData object.');
        return $this;
    }
    
    function output(){
        $this->mySQLQuery = 'INSERT INTO `'. $this->mySQLTable->mySQLTable . '` ' . $this->mySQLData->output();
        return $this->mySQLQuery;
    }
}

class mySQLQueryUpdate extends mySQLQuery{
    function __construct(){
        $this->mySQLTable = null;
        $this->mySQLData = null;
        $this->mySQLWhere = null;
        $this->mySQLQuery = null;
    }
    
    function getColumnCount(){
        return count($this->mySQLData->getData());
    }
    
    function setData($data){
        if(get_class($data)=='mySQLQueryData'){
            $this->mySQLData = $data->setTypeToUpdate();
            $this->mySQLData->mySQLQueryClass = $this;
        }
        else user_error('Property one of setData must be a mySQLQueryData object.');
        return $this;
    }
    
    function output(){
        $this->mySQLQuery = 'UPDATE `' . $this->mySQLTable->mySQLTable . '` SET ' . $this->mySQLData->output() . $this->getWhere();
        return $this->mySQLQuery;
    }
}

class mySQLQuerySelect extends mySQLQuery{
    function __construct(){
        $this->mySQLTable = null;
        $this->mySQLAdditionalTables = array();
        $this->mySQLColumns = array();
        $this->mySQLWhere = null;
        $this->mySQLQuery = null;
        $this->mySQLLimit = array(0,30);
    }
    
    function addColumn($column){
        if(is_string($column)){
            $column = new mySQLColumn($column);
            if($column->Table == '')$column->Table = $this->mySQLTable;
            else if ($column->Table != $this->mySQLTable)
                $this->mySQLAdditionalTables[] = $column->Table;
        }
        else if(get_class($column) != 'mySQLColumn'){
            user_error('Property one of addColumn must be a mySQLColumn object.');
            return $this;
        }
        
        if($column->Table == '')$column->Table = $this->mySQLTable;
        else if ($column->Table != $this->mySQLTable)
            $this->mySQLAdditionalTables[] = $column->Table;
        $this->mySQLColumns[] = $column;
        
        return $this;
    }
    
    function getColumnCount(){
        return count($this->mySQLColumns);
    }
    
    function setLimit($from,$to){
        $this->mySQLLimit = array($from,$to);
    }
    
    function getColumnsOutput(){
        $output = '';
        foreach($this->mySQLColumns as $column){
            $output .= $output == '' ? "`{$column->Table->mySQLTable}`.`{$column->Field}`" : ", `{$column->Table->mySQLTable}`.`{$column->Field}`";
        }
        return $output;
    }
    
    function getTablesOutput(){
        $output = "`{$this->mySQLTable->mySQLTable}`";
        foreach($this->mySQLAdditionalTables as $table)
            $output .= ", `{$table->mySQLTable}`";
        return $output;
    }
    
    function output(){
        list($limitFrom,$limitTo) = $this->mySQLLimit;
        $this->mySQLQuery = 'SELECT ' . $this->getColumnsOutput() . ' FROM ' . $this->getTablesOutput() . $this->getWhere() . ' LIMIT ' . $limitFrom . ', ' . $limitTo;
        return $this->mySQLQuery;
    }
}

/////////////////////////////////////

class mySQLQueryData{
    function __construct($dataArray = null){
        $this->data = $dataArray == null ? array() : $dataArray;
    }
    function getData(){
        return $this->data;
    }
    function addSetter($column,$value){
        if(get_class($column) == 'mySQLColumn')$this->data[] = array($column,$value);
        else if(is_string($column))$this->data[] = array(new mySQLColumn($column),$value);
        else user_error('First parameter of addSetter must be a string, or a mySQLColumn class.');
        return $this;
    }
    function setTypeToInsert(){
        return new __mySQLQueryData($this,'INSERT');
    }
    function setTypeToUpdate(){
        return new __mySQLQueryData($this,'UPDATE');
    }
}

class __mySQLQueryData{
    function __construct($dataClass,$method){
        if(get_class($dataClass) != 'mySQLQueryData')user_error('__mySQLQueryData can only be called from mySQLQueryData.');
        $this->method = $method;
        $this->data = $dataClass->data;
        $this->output = null;
        $this->mySQLQueryClass = null;
    }
    function output(){
        if(!isset($this->output)){
            switch($this->method){
                case 'INSERT':
                    $insert_key = '';
            		$insert_value = '';
            		foreach ($this->data as $array){
            		    list($key,$value) = $array;
            		    $key = $key->Field;
            		    $value = mysql_real_escape_string($value);
            			$insert_key = $insert_key == '' ? "`$key`" : $insert_key . ", `$key`";
            			$insert_value = $insert_value == '' ? "'$value'" : $insert_value . ", '$value'";
            		}
            		$this->output = '('. $insert_key . ') VALUES (' . $insert_value . ')';
                break;
                case 'UPDATE':
                    $return = '';
            		foreach ($this->data as $array){
            		    list($key,$value) = $array;
            		    if($key->Table == '')$key->Table = $this->mySQLQueryClass->mySQLTable;
            		    $column = $key->Field;
            		    $table = '`'.$key->Table->mySQLTable.'`';
            		    $value = mysql_real_escape_string($value);
            			$return .= $return == '' ? "$table.`$column` = '$value'" : ", $table.`$column` = '$value'";
            		}
            		$this->output = $return;
                break;
            }
        }
    	return $this->output;
    }
}

/////////////////////////////////////

class mySQLOperatorIsEqualTo{
    var $property = '=';
}
class mySQLOperatorIsNotEqualTo{
    var $property = '!=';
}
class mySQLOperatorIsGreaterThan{
    var $property = '>';
}
class mySQLOperatorIsLessThan{
    var $property = '<';
}
class mySQLOperatorIsGreaterThanOrEqualTo{
    var $property = '>=';
}
class mySQLOperatorIsLessThanOrEqualTo{
    var $property = '<=';
}
class mySQLOperatorIn{
    var $property = 'IN';
}

class mySQLClause{
    function __construct($column = null, $clause = null, $value = null){
        $this->column = null;
        $this->clause = null;
        $this->value = null;
        if($column != null)$this->setColumn($column);
        if($clause != null)$this->setClause($clause);
        if($value != null)$this->setValue($value);
    }
    function setColumn($column){
        if(is_string($column))$this->column = new mySQLColumn($column);
        else if(get_class($column) == 'mySQLColumn')$this->column = $column;
        else user_error('setColumn requires first argument to be either a string or a mySQLColumn object.');
        return $this;
    }
    function setClause($clause){
        if(strpos(get_class($clause),'mySQLOperator') !== false)$this->clause = $clause;
        else user_error('Clause provided to setClause is not a clause object.');
        return $this;
    }
    function setValue($value){
        if(is_string($value) || is_numeric($value) || (get_class($this->clause) == 'mySQLOperatorIn' && get_parent_class($value) == 'mySQLQuery'))$this->value = $value;
        else user_error('Value provided to setValue is not a string, an integer, or a mySQLQuery object.');
        return $this;
    }
    function output(){
        if($this->column != null && $this->clause != null && $this->value != null){
            $fieldName = $this->column->getField();
            $clause = $this->clause->property;
            if($this->column->Table != ''){
                $table = '`'.$this->column->Table->mySQLTable.'`.';
            }
            else $table = '';
            if(get_class($this->clause) == 'mySQLOperatorIn'){
                $output = $table . '`' . $fieldName . '` ' . $clause . ' (' .  $this->value->output() . ')';
            }
            else{
                if(is_numeric($this->value))
                    $output = $table . '`' . $fieldName . '` ' . $clause . ' ' .  $this->value . '';
                else {
                    $output = $table . '`' . $fieldName . '` ' . $clause . ' \'' .  mysql_real_escape_string($this->value) . '\'';
                }
            }
            return $output;
        }
        else user_error('Column, clause, and value must be set for mySQLClause before you can use it.');
    }
}

class mySQLWhereClause{
    function __construct(){
        $this->mySQLQueryClass = null;
        $this->mySQLWhereArray = array();
    }
    function addClause(){
        $numberofArgs = func_num_args();
        $args = func_get_args();
        if($numberofArgs == 1){
            $clause = $args[0];
            if(get_class($clause)=='mySQLClause'){
                $this->mySQLWhereArray[] = $clause;
            }
            else user_error('Clause provided to addClause is not a clause object.');
        }
        else if($numberofArgs == 3)
            $this->mySQLWhereArray[] = new mySQLClause($args[0],$args[1],$args[2]);
        
        return $this;
    }
    function output(){
        $output = '';
        foreach($this->mySQLWhereArray as $mySQLClauseClass){
            if($mySQLClauseClass->column->Table == '')$mySQLClauseClass->column->setTable($this->mySQLQueryClass->mySQLTable);
            $output .= $output == '' ? 'WHERE ' . $mySQLClauseClass->output() : ' AND ' . $mySQLClauseClass->output();
        }
        return $output;
    }
}

/////////////////////////////////////

class mySQLColumn{
    function __construct($field = ''){
        $this->Field = $field;
        $this->Type = '';
        $this->Null = '';
        $this->Key = '';
        $this->Default = '';
        $this->Extra = '';
        $this->Length = '';
        $this->Table = '';
    }
    public function __call($name, $args){
        if(preg_match('/^([gs]et_?)(.+)/', $name, $parts)){
            $attribute = $parts[2];
            // needs to be fixed ... at a later time, ugh.
            $first = isset($this->{ucfirst($attribute)});
            $second = isset($this->{lcfirst($attribute)});
            if($first || $second){
                if(!($first && $second))
                    $attribute = $first ? ucfirst($attribute) : lcfirst($attribute);
                $action = rtrim($parts[1], '_');
                switch($action){
                    case 'get': 
                            return $this->$attribute;
                    case 'set':
                        $this->$attribute = $args[0];
                        return $this;
                    break;
                }
            }
            else {
                user_error('Attribute "' . $attribute . '" does not exist for mySQLColumn class.');
                return false;
            }
        }
        trigger_error("Unknown method: $name()");
    }
    function loadFromArray($array){
        foreach($array as $key=>$value){
            $this->$key = $value;
        }
    }
    function isPrimary(){
        return $this->Key === 'PRI' ? true : false;
    }
    function setTable($table){
        if(get_class($table) == 'mySQLTable')$this->Table = $table;
        else if(is_string($table))$this->Table = new mySQLTable($table);
        else user_error('First parameter of setTable must either be a string or a mySQLTable object.');
    }
}

class mySQLTable{
    function __construct($mySQLTable){
        global $conn;
        $this->mySQLTable = $mySQLTable;
        $this->mySQLConnection = $conn;
        $this->mySQLTablePrimaryKey = null;
        $this->mySQLTableColumns = array();
        $this->mySQLTableWhereClause = null;
        $this->mySQLTableRows = array();
    }
    function fetchColumns(){
        if($this->mySQLTable != null){
            if(count($this->mySQLTableColumns) == 0){
                $query = mysql_query('DESCRIBE `'.$this->mySQLTable.'`',$this->mySQLConnection) or die(mysql_error());
                if(mysql_num_rows($query) == 0)$this->error('Table not found.',true);
                else {
                    while($array = mysql_fetch_assoc($query)){
                        $columnArray = $array;
                        $column = new mySQLColumn($array['Field']);
                        $column->setTable($this);
                        if(preg_match('!(\w*)\((\d*)\)!',$array['Type'],$matches)){
                            $array['Type']=$matches[1];
                            $array['Length']=$matches[2];
                        }
                        $column->loadFromArray($array);
                        if($column->isPrimary())$this->mySQLTablePrimaryKey=$column;
                        $this->mySQLTableColumns[$array['Field']] = $column;
                    }
                }
            }
            return $this->mySQLTableColumns;
        }
        else $this->error('No table was set.');
    }
    function fetchColumn($index){
        if(count($this->mySQLTableColumns) != 0){
            return $this->mySQLTableColumns[$index];
        }
        else {
            $this->fetchColumns();
            return $this->fetchColumn($index);
            
        }
    }
    function getPrimaryKey(){
        $this->fetchColumns();
        return $this->mySQLTablePrimaryKey;
    }
    function setWhere($where){
        if(get_class($where) == 'mySQLWhereClause'){
            $this->mySQLTableWhereClause = $where;
        }
        else if(get_class($where) == 'mySQLClause'){
            $newWhere = new mySQLWhereClause;
            $newWhere->addClause($where);
            $this->mySQLTableWhereClause=$newWhere;
        }
        else user_error('Property one of setWhere must be a mySQLWhereClause object.');
    }
    function isSelection(){
        if(isset($this->mySQLTableWhereClause))return true;
        else return false;
    }
    function getRows($simpleArray = false){
        if(count($this->mySQLTableRows)==0)$this->grabRows();
        if($simpleArray){
            $return = array();
            foreach($this->mySQLTableRows as $row){
                $return[] = $row->getData();
            }
            return $return;
        }
        else {
            $return = array();
            foreach($this->mySQLTableRows as $row){
                $newRow = new mySQLRow($row);
                $newRow->setTable($this);
                $newRow->mySQLConnecton = $this->mySQLConnection;
                $return[$row[$this->mySQLTablePrimaryKey->Field]] = $newRow;
            }
            return $return;
        }
    }
    function grabRows(){
        $this->fetchColumns();
        $mySQLSelectClass_Query = new mySQLQuerySelect;
        $mySQLSelectClass_Query->setTable($this);
        if(isset($this->mySQLTableWhereClause)){
            $this->mySQLTableWhereClause->mySQLQueryClass = $mySQLSelectClass_Query;
            $mySQLSelectClass_Query->setWhere($this->mySQLTableWhereClause);
        }
        foreach($this->mySQLTableColumns as $field=>$column)$mySQLSelectClass_Query->addColumn($column);
        $mySQLClass = new mySQL($mySQLSelectClass_Query,$this->mySQLConnection);
        $return = array();
        $array = $mySQLClass->fetchArray(true,MYSQL_ASSOC);
		if(is_array($array)){
	        foreach($array as $row){
	            $newRow = new mySQLRow($row);
	            $newRow->setTable($this);
	            $newRow->mySQLConnecton = $this->mySQLConnection;
	            $return[$row[$this->mySQLTablePrimaryKey->Field]] = $newRow;
	        }
	        $this->mySQLTableRows = $return;
	        return $this->mySQLTableRows;
		}
		else return array();
    }

	function newRow(){
		$newRow = new mySQLRow(null,true);
		$newRow->setTable($this);
		return $newRow;
	}
}

class mySQLRow{
    function __construct($mySQLRowData = null,$new = false){
        global $conn;
        $this->mySQLConnection = $conn;
        $this->mySQLTable = null;
        $this->mySQLRowData = array();
        $this->mySQLRowDataChanges = array();
        if(is_array($mySQLRowData))$this->mySQLRowData = $mySQLRowData;
        $this->loadedFromDatabase = false;
		$this->isNewRow = $new;
    }
    
    public function __call($name, $args){
        if(preg_match('/^([gs]et_?)(.+)/', $name, $parts)){
            if($this->mySQLTable != null){
                $column = $parts[2];
                $first = array_key_exists(ucfirst($column),$this->mySQLRowData);
                $second = array_key_exists(lcfirst($column),$this->mySQLRowData);
                if($first || $second){
                    if(!($first && $second))
                        $column = $first ? ucfirst($column) : lcfirst($column);
                    
                    $action = rtrim($parts[1], '_');
                    switch($action){
                        case 'set':
                            $value = array_shift($args);
                            $this->mySQLRowData[$column] = $value;
                            $this->mySQLRowDataChanges[$column] = $value;
                            return $this;
                        break;
                        case 'get': 
                                return $this->mySQLRowData[$column];
                        break;
                    }
                }
                else {
                    $this->error('Column "' . $column . '" does not exist in table "'. $this->mySQLTable->mySQLTable .'".');
                    return false;
                }
            }
            else {
                $this->error('You must set the table before you can make changes to it.');
                return false;
            }
        }
        trigger_error("Unknown method: $name()");
    }
    
    private function error($message,$critical = false){
        if($critical)die($message);
        else echo $message;
    }
    
    public function getData(){
        return $this->mySQLRowData;
    }
    
    public function setTable($table){
        if(is_string($table)){
            $table = new mySQLTable($table);
            $table->mySQLConnection = $this->mySQLConnection;
        }
        else if(get_class($table) != 'mySQLTable'){
            user_error('Property one of setTable must either be a string or a mySQLTable object.');
            return $this;
        }
        $this->mySQLTable = $table;
        $this->fetchColumns();
        return $this;
    }
    
    public function loadData(){
        if(!$this->loadedFromDatabase){
            $mySQLQuerySelect = new mySQLQuerySelect;
            $mySQLQuerySelect->setTable($this->mySQLTable);
            foreach($this->mySQLTable->mySQLTableColumns as $column)$mySQLQuerySelect->addColumn($column);
            $whereClause= new mySQLWhereClause;
            foreach($this->mySQLRowDataChanges as $column => $value){
                if($value != '')
                    $whereClause->addClause($this->mySQLTable->fetchColumn($column),new mySQLOperatorIsEqualTo,$value);
            }
            $mySQLQuerySelect->setWhere($whereClause);
            $mySQLClass = new mySQL($mySQLQuerySelect, $this->mySQLConnection);
            if($mySQLClass->doesExist()){
                $this->mySQLRowData = $mySQLClass->fetchArray(false,MYSQL_ASSOC);
                $this->mySQLRowDataChanges = array();
                $this->loadedFromDatabase = true;
            }
            else $this->error('No row was returned.');
        }
    }
    
    public function updateData($insert = false){
        $queryData = new mySQLQueryData;
        foreach($this->mySQLRowDataChanges as $column => $value){
            if($value != null){
                $queryData->addSetter($this->mySQLTable->fetchColumn($column),$value);
            }
        }
        
		if($this->isNewRow){
			$mySQLQuery = new mySQLQueryInsert;
			$mySQLQuery->setTable($this->mySQLTable)
						->setData($queryData);
		}
		else {
			$primaryKey = $this->mySQLTable->getPrimaryKey();
        	$mySQLQuery = new mySQLQueryUpdate;
	        $mySQLQuery    ->setTable($this->mySQLTable)
	                        ->setData($queryData)
	                        ->setWhere(
								new mySQLClause(
									$primaryKey,
									new mySQLOperatorIsEqualTo,
									$this->mySQLRowData[$primaryKey->Field])
								);
		}
        $mySQL = new mySQL($mySQLQuery,$this->mySQLConnection);
        $return = $mySQL->executeQuery();
        if($return){
            $this->mySQLRowDataChanges = array();
            return true;
        }
        else return $return;
    }
    
    private function fetchColumns(){
        if($this->mySQLTable != null){
            if(count($this->mySQLRowData) == 0){
                foreach($this->mySQLTable->fetchColumns() as $column){
                    $this->mySQLRowData[$column->Field] = null;
                }
            }
        }
        else $this->error('No table was set.');
    }
    function __destruct(){
        
    }
}
?>