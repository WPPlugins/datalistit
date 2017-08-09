<?php

class DLI_TYPE
{
	const DLI_ALL = 0;
	const DLI_STRING = 1;
	const DLI_INT = 2;
	const DLI_FLOAT = 4;
	const DLI_DATE = 8;

	const DLI_NUMBERS = 0x0E;
}

class DbTable
{
	const MAX_HTML_ROWS=500;
	const TABLE_PREFIX = 'dli_';
	const NUMBER_COLUMN = "c";
	const STRING_COLUMN = "s_c";
	const NO_ROWS = 10;
	private $toupleColumns = array();
	
	private $filename;
	public $columns;
	private $orderBy ;
	public  $noRows ;
	private $columnsType;
	
	private $columnsFlip;

	public $selectedColumns;
	public $selectedOrderBy;
	
	public $htmlOutput;

	function __construct( $filename,$columns, $orderBy, $noRows, $columnsType=null )
	{
		$this->filename = $filename;
		$this->columns = $columns;
		$this->orderBy = $orderBy;
		$this->noRows = intval($noRows);  if( !$this->noRows) $this->noRows = self::NO_ROWS;
		$this->columnsType = $columnsType;
	}

	private function trim_columns(&$item)
	{
        $item = trim(strtolower($item));
	}

	private function CheckType( $i)
	{
		$type = $this->columnsType[$i];
		if($type == DLI_TYPE::DLI_STRING)
		{
			$ret =  array(self::STRING_COLUMN.$i, false);
		}
		else if ($type & DLI_TYPE::DLI_NUMBERS && !($type & DLI_TYPE::DLI_STRING))
		{
			$ret =  array(self::NUMBER_COLUMN.$i, false);
		}
		else 
		{
			//string column first
			$ret = array(self::STRING_COLUMN.$i.",".self::NUMBER_COLUMN .$i, true);
		}
		return $ret;
	}

	function SetOption()
	{	
		array_walk($this->columns, array($this,'trim_columns'));

		$this->columnsFlip =  array_flip($this->columns);

		foreach(  $this->columnsFlip as &$column)
		{ 
			$column = self::CheckType($column );
		}
		$this->orderBy = array();
		update_option(	self::TABLE_PREFIX.$this->filename."_columns",  $this->columnsFlip);

	}

	function GetOption()
	{
		//columnsFlip: [column name => [select column, touple type],... ]
		$this->columnsFlip = get_option(self::TABLE_PREFIX.$this->filename."_columns");

		$this->columns = strlen($this->columns) ? explode(",", $this->columns) : array("all");
		$this->orderBy = strlen($this->orderBy) ? explode(",", $this->orderBy) : array();

	}

	function Run()
	{	
		$this->trim_columns($this->columns[0]);
		if ( count($this->columns)==1  && $this->columns[0] == "all")
		{
			$this->columns =  array_keys( $this->columnsFlip);
		}
		array_walk($this->columns, array($this,'trim_columns'));
		array_unique($this->columns); //TODO: because wpdb class query distinct selects 
		array_walk($this->orderBy, array($this,'trim_columns'));
		

		//create select column list
		foreach( $this->columns as $c)
		{
			$val = &$this->columnsFlip[$c][0];
			$this->selectedColumns .= 
				$this->columnsFlip[$c][1] 
					? "CONCAT( $val),"
					: "$val,";
		}
		foreach( $this->orderBy as $ob)
		{
			$this->selectedOrderBy .= $this->columnsFlip[$ob][0].",";
		}
		//remove last ','
		$this->selectedColumns = substr_replace($this->selectedColumns ,"",-1);
		$this->selectedOrderBy = substr_replace($this->selectedOrderBy ,"",-1);
	}
	
	function OutputHTML()
	{
		global  $wpdb, $table_prefix;
		
		if ( strlen($this->selectedOrderBy)) $this->selectedOrderBy = "ORDER BY " . $this->selectedOrderBy;
		
		$this->sqlResult = $wpdb->get_results( 
		"SELECT $this->selectedColumns FROM ".$table_prefix.self::TABLE_PREFIX."$this->filename $this->selectedOrderBy LIMIT 0," . self::MAX_HTML_ROWS,
		ARRAY_N);

		$output = '<table class="dli_table" ><tr>';
		
		//for all columns
		foreach( $this->columns as $name )
		{
			$output.= "<th>$name</th>";
		}
		
		$output.= "</tr>";
		$i = 0;
		foreach ( $this->sqlResult as $row)
		{
			if( $i %2 ) $output= $output."<tr class='even'>";
			else $output= $output."<tr class='odd' >";

			foreach ( $row as $c)
			{	
				$output .= "<td> $c </td>";
			}
			$output.= "</tr>";
			$i++;
		}
		$output.= "</table>";
		
		$this->htmlOutput= $output;
	}
}
?>
