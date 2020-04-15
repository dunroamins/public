<?php
include_once 'Config.php';
require_once '/thirdparty/aws-autoloader.php';
use Aws\S3\S3Client;
class IFD
{
    var $m_IFDName;
    public function __construct()
    {
        
    }
    public function __destruct()
    {
        foreach ($this as $index => $value)
            unset($this->$index);
    }
    function SetIFDName($name, $xsdType = '', $xsdElement = '')
    {
        $this->m_IFDName = new ValueType($xsdType, $xsdElement);
        $this->m_IFDName->SetValue($name);
    }
    function DefaultIFDName()
    {
        $default = get_class($this);
        $this->SetIFDName($default, ('tns:' . $default), $default);
    }
}
class DataSet
{
    var $m_UniqueLoggingId;
    var $m_LogFileName = 'DataSetCMD.log';
    var $m_InsertId;
    var $m_TableName;
    var $m_TableAlias;
    var $m_Mysql = NULL;
    var $m_ResultSet = array();
    var $m_Columns = array();
    var $m_JoinedColumnList = array();
    var $m_JoinedToColumnList = array();
    var $m_TableList = array();
    var $m_JoinType;
    var $m_IncludeColumns = 'ALL';
    var $m_WhereClause;
    var $m_RowOffset = 0;
    public function __construct()
    {
        $this->m_UniqueLoggingId = rand(10000000, 99999999999);
        $this->SetDBLabels();
    }
    public function __destruct()
    {
        foreach ($this as $index => $value)
            unset($this->$index);
    }
    function DebugLog($text, $failure = false)
    {
        $client = S3Client::factory(array(
            'key' => AWS_KEY,
            'secret' => AWS_SECRET
        ));
        $client->registerStreamWrapper();
        $clientIP = '';
        $backTrace = '';
        $function = '';
        if (stristr(LOG_LEVEL, 'D'))
        {
            $fileName = LOG_DIRECTORY . '/' . LOG_DATASET_FILENAME . '_' . date('Ymd');
            $fileHandle = -1;
                if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
                    $clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
                else
                    $clientIP = $_SERVER['REMOTE_ADDR'];
                $backTrace = debug_backtrace();
                if (!isset($backTrace[3]))
                    $function = $backTrace[2]['function'] . '.' . $backTrace[1]['function'];
                else
                    $function = $backTrace[3]['function'] . '.' . $backTrace[1]['function'];
                if (strstr($text, 'SHA1'))
                    $text = preg_replace("/SHA1\('.+'\)/", "SHA1('**********')", $text);
                $fileHandle = fopen('s3://' . $fileName, 'a');
                fwrite($fileHandle, date('Y-m-d G:i:s') . ' (' . $this->m_UniqueLoggingId . ') ' . '[' . $clientIP . '] ' . ($failure ? '***FAILED*** ' : '') . get_class($this) . '.' . $function . ' ' . preg_replace('/\n*|\r*/', '', $text));
                fwrite($fileHandle, PHP_EOL . '---------------------------------------------------------------' . PHP_EOL);
                fclose($fileHandle);
        }
    }
    function SetDBLabels()
    {
        if (!defined('DBWHERE'))
            DEFINE('DBWHERE', ' WHERE ');
        if (!defined('DBAND'))
            DEFINE('DBAND', ' AND ');
        if (!defined('DBOR'))
            DEFINE('DBOR', ' OR ');
        if (!defined('DBLIKE'))
            DEFINE('DBLIKE', ' LIKE ');
        if (!defined('DBEQUALS'))
            DEFINE('DBEQUALS', ' = ');
        if (!defined('DBNOTEQUALS'))
            DEFINE('DBNOTEQUALS', ' <> ');
        if (!defined('DBISNULL'))
            DEFINE('DBISNULL', ' IS NULL ');
        if (!defined('DBUPPER'))
            DEFINE('DBUPPER', ' UPPER');
        if (!defined('DBORDER'))
            DEFINE('DBORDER', ' ORDER BY ');
        if (!defined('DBGROUP'))
            DEFINE('DBGROUP', ' GROUP BY ');
        if (!defined('DBIN'))
            DEFINE('DBIN', ' IN ');
        if (!defined('DBDATE'))
            DEFINE('DBDATE', ' DATE_FORMAT');
        if (!defined('DBBETWEEN'))
            DEFINE('DBBETWEEN', ' BETWEEN ');
        if (!defined('DBDESC'))
            DEFINE('DBDESC', ' DESC');
    }
    function Comparison($column, $value)
    {
        if (preg_match('/^(nullvalue|null|n\/a)$/i', $value))
            return ' ' . $column . DBISNULL;
        $value = str_replace("'", "''", $value);
        if (preg_match('/^\<|\>/', $value))
            return ' ' . $column . preg_replace('/^(\<|\>)(.*)/', ' \1 \'\2\'', $value);
        elseif (strpos($value, '*') === false)
            return ' ' . DBUPPER . '(' . $column . ') ' . DBEQUALS . '\'' . strtoupper($value) . '\'';
        else
        {
            $value = str_replace('*', '%', $value);
            return ' ' . DBUPPER . '(' . $column . ') ' . DBLIKE . '\'' . strtoupper($value) . '\'';
        }
    }
    function DateFormatStatement($column, $format)
    {
        return DBDATE . '(' . $column . ',"' . $format . '") ';
    }
    function InsertStatement()
    {
        $insertString = "INSERT INTO `$this->m_TableName` (";
        foreach ($this->m_Columns as $column)
        {
            if (!$column->IsPrimaryKey())
                $insertString .= $column->GetColumnName() . ', ';
        }
        $insertString = preg_replace('/,\s$/', '', $insertString);
        $insertString .= ') VALUES (';
        foreach ($this->m_Columns as $column)
        {
            if (!$column->IsPrimaryKey())
                $insertString .= $column->InsertSQL() . ', ';
        }
        $insertString = preg_replace('/,\s$/', '', $insertString);
        $insertString .= ')';
        return $insertString;
    }
    function UpdateStatement($inMass = false)
    {
        $primaryString = '';
        $updateString = "UPDATE `$this->m_TableName` $this->m_TableAlias SET ";
        if ($inMass)
        {
            foreach ($this->m_Columns as $column)
                if ($column->IsUpdate())
                    $updateString .= $column->GetColumnName() . DBEQUALS . $column->InsertSQL() . ', ';
        }
        else
        {
            foreach ($this->m_Columns as $column)
            {
                if ($column->IsPrimaryKey())
                    $primaryString .= $column->GetColumnName() . ' = ' . $column->GetValue() . DBAND;
                elseif ($column->IsValid())
                    $updateString .= $column->GetColumnName() . ' = ' . $column->InsertSQL() . ', ';
            }
            $primaryString = preg_replace('/\sAND\s$/', '', $primaryString);
        }
        $updateString = preg_replace('/,\s$/', '', $updateString);
        if ($inMass)
            $updateString .= ' ' . $this->m_WhereClause;
        else
            $updateString .= DBWHERE . $primaryString;
        return $updateString;
    }
    function DeleteStatement($inMass = false)
    {
        $deleteString = '';
        $deleteString = "DELETE $this->m_TableAlias FROM `$this->m_TableName` $this->m_TableAlias ";
        if ($inMass)
        {
            $deleteString .= $this->BuildJoinedTableList($this);
            if ($this->m_WhereClause == '')
                return '';
            else
            {
                $pos = strpos($this->m_WhereClause, DBORDER);
                if ($pos !== FALSE)
                    $this->m_WhereClause = substr($this->m_WhereClause, 0, $pos);
                $deleteString .= ' ' . $this->m_WhereClause;
            }
        }
        else
        {
            $deleteString .= DBWHERE;
            foreach ($this->m_Columns as $column)
            {
                if ($column->IsPrimaryKey())
                    $deleteString .= $column->GetColumnName() . ' = ' . $column->GetValue() . DBAND;
            }
            $deleteString = preg_replace('/\sAND\s$/', '', $deleteString);
        }
        return $deleteString;
    }
    function SelectStatement()
    {
        $selectString = 'SELECT ';
        $selectString .= $this->BuildColumnList($this);

        $selectString = preg_replace('/,\s$/', '', $selectString);
        $selectString .= " FROM `$this->m_TableName` $this->m_TableAlias";

        $selectString .= $this->BuildJoinedTableList($this);
        $selectString .= ' ' . $this->m_WhereClause;

        if (strpos($selectString, ' LIMIT ') === false)
            $selectString .= ' LIMIT ' . $this->m_RowOffset . ',' . DB_MAX_ROW;

        return $selectString;
    }
    function WhereClause()
    {
        return $this->m_WhereClause;
    }
    function BuildColumnList($ds)
    {
        $columnList = $this->GetColumns($ds);
        foreach ($ds->m_TableList as $table)
            $columnList .= $this->BuildColumnList($table);
        return $columnList;
    }
    function GetColumns($ds)
    {
        $columnString = '';
        foreach ($ds->m_Columns as $column)
        {
            if ($column->IsActive())
                $columnString .= 'IFNULL(' . $column->NameAlias() . ", 'nullvalue'), ";
        }
        return $columnString;
    }
    function BuildJoinedTableList($ds)
    {
        $joinList = '';
        foreach ($ds->m_TableList as $table)
        {
            switch ($table->m_JoinType)
            {
                case 'STANDARD':
                    $joinList .= $this->StandardJoinStatement($table);
                    break;
                case 'LEFT':
                    $joinList .= $this->LeftJoinStatement($table);
                    break;
            }
            $joinList .= $this->BuildJoinedTableList($table);
        }
        return $joinList;
    }
    function StandardJoinStatement($table)
    {
        $joinString = '';
        $joinString .= " JOIN `$table->m_TableName` $table->m_TableAlias ON ";
        for ($i = 0, $max = sizeof($table->m_JoinedColumnList); $i < $max; ++$i)
            $joinString .= $table->m_JoinedColumnList[$i] . ' = ' . $table->m_JoinedToColumnList[$i] . DBAND;
        $joinString = preg_replace('/\sAND\s$/', '', $joinString);
        return $joinString;
    }
    function LeftJoinStatement($table)
    {
        $joinString = '';
        $joinString .= " LEFT JOIN `$table->m_TableName` $table->m_TableAlias ON ";
        for ($i = 0, $max = sizeof($table->m_JoinedColumnList); $i < $max; ++$i)
            $joinString .= $table->m_JoinedColumnList[$i] . ' = ' . $table->m_JoinedToColumnList[$i] . DBAND;
        $joinString = preg_replace('/\sAND\s$/', '', $joinString);
        return $joinString;
    }
    function Insert()
    {
        $retVal = false;

        $insertString = $this->InsertStatement();

        if ($this->ConnectDatabase())
        {
            if ($this->m_Mysql->query($insertString))
            {
                $this->DebugLog($insertString);
                $this->m_InsertId = $this->m_Mysql->insert_id;
                $retVal = true;
            }
            else
                $this->DebugLog(mysqli_errno($this->m_Mysql) . ': ' . mysqli_error($this->m_Mysql), true);
            $this->m_Mysql->close();
            unset($this->m_Mysql);
        }
        return $retVal;
    }
    function Update($inMass = false) // $inMass is a mass update (dolist) - updating many rows at once
    {
        $retVal = false;
        $updateString = $this->UpdateStatement($inMass);

        if ($this->ConnectDatabase())
        {
            if ($this->m_Mysql->query($updateString))
            {
                $this->DebugLog($updateString);
                $retVal = true;
            }
            else
                $this->DebugLog(mysqli_errno($this->m_Mysql) . ': ' . mysqli_error($this->m_Mysql), true);
            $this->m_Mysql->close();
            unset($this->m_Mysql);
        }

        return $retVal;
    }
    function Delete($inMass = false) // $inMass is a mass delete (dolist) - deleting many rows at once
    {
        $retVal = false;
        $deleteString = $this->DeleteStatement($inMass);

        if ($this->ConnectDatabase())
        {
            if ($this->m_Mysql->query($deleteString))
            {
                $this->DebugLog($deleteString);
                $retVal = true;
            }
            else
                $this->DebugLog(mysqli_errno($this->m_Mysql) . ': ' . mysqli_error($this->m_Mysql), true);
            $this->m_Mysql->close();
            unset($this->m_Mysql);
        }

        return $retVal;
    }
    function Select($rowOffSet = 0)
    {
        $retVal = false;
        $this->m_RowOffset = $rowOffSet;

        $queryString = $this->SelectStatement();

        if ($this->ConnectDatabase())
        {
            if ($resultSet = $this->m_Mysql->query($queryString))
            {
                $this->DebugLog($queryString);
                while ($this->m_ResultSet[] = $resultSet->fetch_array(MYSQLI_NUM));
                $resultSet->close();
                unset($resultSet);
                $retVal = true;
            }
            else
                $this->DebugLog(mysqli_errno($this->m_Mysql) . ': ' . mysqli_error($this->m_Mysql), true);
            $this->m_Mysql->close();
            unset($this->m_Mysql);

            if ($retVal)
                $retVal = $this->Fetch();
        }

        return $retVal;
    }
    function SelectCount()
    {
        $count = -1;

        $selectString = "SELECT COUNT(*) FROM `$this->m_TableName` $this->m_TableAlias ";

        $selectString .= $this->BuildJoinedTableList($this);
        $selectString .= ' ' . $this->m_WhereClause;

        if ($this->ConnectDatabase())
        {
            if ($resultSet = $this->m_Mysql->query($selectString))
            {
                $this->DebugLog($selectString);
                while ($this->m_ResultSet[] = $resultSet->fetch_array(MYSQLI_NUM));

                $count = $this->m_ResultSet[0][0];
                $resultSet->close();
                unset($resultSet);
            }
            else
                $this->DebugLog(mysqli_errno($this->m_Mysql) . ': ' . mysqli_error($this->m_Mysql), true);
        }

        return $count;
    }
    function ConnectDatabase()
    {
        $retVal = true;

        $this->m_Mysql = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

        if (mysqli_connect_errno())
        {
            $this->DebugLog('Error connecting to the database: ' . DB_DATABASE . ' (' . mysqli_connect_errno() . '): ' . mysqli_connect_error(), true);
            $retVal = false;
        }

        return $retVal;
    }
    function Load()
    {
        
    }
    function Fetch($which = 0)
    {
        if (empty($this->m_ResultSet[$which]))
            return false;
        $ctr = -1;
        $this->FillColumns($this, $ctr, $this->m_ResultSet[$which]);
        return true;
    }
    function FillColumns(&$ds, &$colCtr, $resultSetRow)
    {
        foreach ($ds->m_Columns as $key => $column)
        {
            if ($column->IsActive() && isset($resultSetRow[++$colCtr]))
                $ds->m_Columns[$key]->SetValue($resultSetRow[$colCtr]);
        }
        for ($i = 0, $max = sizeof($ds->m_TableList); $i < $max; $i++)
            $this->FillColumns($ds->m_TableList[$i], $colCtr, $resultSetRow);
    }
    function IsPrimaryKeySet()
    {
        foreach ($this->m_Columns as $column)
        {
            if ($column->IsPrimaryKey() && $column->IsEmpty())
                return false;
        }
        return true;
    }
    function SetPrimaryKey()
    {
        foreach ($this->m_Columns as $key => $value)
        {
            if ($this->m_Columns[$key]->IsPrimaryKey())
                $this->m_Columns[$key]->SetValue($this->m_InsertId);
        }
    }
    function ActivateAllColumns()
    {
        foreach ($this->m_Columns as $key => $value)
            $this->m_Columns[$key]->Activate();
    }
    function DeactivateAllColumns()
    {
        foreach ($this->m_Columns as $key => $value)
            $this->m_Columns[$key]->Deactivate();
    }
    function AddJoinedColumn($column, $toColumn)
    {
        $this->m_JoinedColumnList[] = $column;
        $this->m_JoinedToColumnList[] = $toColumn;
    }
    function JoinTables($fromTable, &$joinedTable, $joinType = '')
    {
        if ($joinType == '')
            $joinType = $this->JoinTypeStandard();
        $joinedTable->m_JoinType = $joinType;
        $fromTable->m_TableList[] = $joinedTable;
    }
    function IncludeColumnsAll()
    {
        $this->m_IncludeColumns = 'ALL';
    }
    function IncludeColumnsNone()
    {
        $this->m_IncludeColumns = 'NONE';
    }
    function JoinTypeStandard()
    {
        return 'STANDARD';
    }
    function JoinTypeLeft()
    {
        return 'LEFT';
    }
    function GetDataSet($name)
    {
        foreach ($this->m_TableList as $table)
        {
            if (is_a($table, $name))
                return $table;
        }
        return null;
    }
}
/* * ************** */
class ValueType
{
    var $m_Value;
    var $m_XsdType;
    var $m_XsdElement;
    var $m_Stream;
    var $m_Update;
    public function __construct($xsdType, $xsdElement, $stream = true)
    {
        $this->m_XsdType = $xsdType;
        $this->m_XsdElement = $xsdElement;
        $this->m_Value = 'nullvalue';
        $this->m_Stream = $stream;
        $this->m_Update = false;
    }
    function GetValue()
    {
        return $this->m_Value;
    }
    function GetOutput()
    {
        if ($this->IsNull())
            return '';
        return $this->m_Value;
    }
    function GetDateValue($dateFormat)
    {
        if ($this->IsEmptyNull())
            return '';
        return date($dateFormat, strtotime($this->m_Value));
    }
    function GetTimeValue()
    {
        if ($this->IsNull())
            return 0;
        return strtotime($this->m_Value);
    }
    function GetSafeValue()
    {
        $this->m_Value = str_replace('<br \/>', "\r\n", $this->m_Value);
        $this->m_Value = html_entity_decode($this->m_Value);
        return $this->m_Value;
    }
    function GetXSDValue()
    {
        switch ($this->m_XsdType)
        {
            case 'xsd:byte':
                if ($this->IsNull())
                    return 255;
                break;
            case 'xsd:unsignedByte':
                if ($this->IsNull())
                    return 510;
                break;
            case 'xsd:short':
                if ($this->IsNull())
                    return 32767;
                break;
            case 'xsd:unsignedShort':
                if ($this->IsNull())
                    return 65534;
                break;
            case 'xsd:int':
            case 'xsd:long':
                if ($this->IsNull())
                    return 2147483647;
                break;
            case 'xsd:unsignedInt':
            case 'xsd:unsignedLong':
            case 'xsd:float':
            case 'xsd:decimal':
                if ($this->IsNull())
                    return (int) 4294967295;
                break;
            case 'xsd:dateTime':
                if ($this->IsNull() || $this->Equals('0000-00-00 00:00:00'))
                    return '1940-01-01T00:00:00';
                else
                    return str_replace(' ', 'T', $this->m_Value);
                break;
            case 'xsd:date':
                if ($this->IsNull() || $this->Equals('0000-00-00'))
                    return '1940-01-01';
                else
                    return str_replace(' ', 'T', $this->m_Value);
                break;
            case 'xsd:string':
                if ($this->IsNull())
                    return 'NULL';
                break;
            case 'xsd:boolean':
                return (bool) $this->m_Value;
                break;
        }
        return $this->m_Value;
    }
    function GetCSVValue()
    {
        $delimiter = '"';
        $needDelimiter = false;
        $formattedValue = '';

        if (preg_match('/,|"|\n|\r/', $this->m_Value))
            $needDelimiter = true;
        $formattedValue = str_replace('"', '""', $this->m_Value);
        if ($needDelimiter)
            return '"' . $formattedValue . '"';
        else
            return $formattedValue;
    }
    function SetValue($value)
    {
        $this->m_Update = true;
        $this->m_Value = $value;
        switch ($this->m_XsdType)
        {
            case 'xsd:byte':
                if ($value == 255)
                    $this->m_Value = 'nullvalue';
                break;
            case 'xsd:unsignedByte':
                if ($value == 510)
                    $this->m_Value = 'nullvalue';
                break;
            case 'xsd:short':
                if ($value == 32767)
                    $this->m_Value = 'nullvalue';
                break;
            case 'xsd:unsignedShort':
                if ($value == 65534)
                    $this->m_Value = 'nullvalue';
                break;
            case 'xsd:int':
            case 'xsd:long':
                if ($value == 2147483647)
                    $this->m_Value = 'nullvalue';
                break;
            case 'xsd:unsignedInt':
            case 'xsd:unsignedLong':
            case 'xsd:float':
            case 'xsd:decimal':
                if ($value == 4294967295)
                    $this->m_Value = 'nullvalue';
                break;
            case 'xsd:dateTime':
                if (strpos($value, '1940-01-01T00:00:00', 0) !== false)
                    $this->m_Value = 'nullvalue';
                break;
            case 'xsd:date':
                if ($value == '1940-01-01')
                    $this->m_Value = 'nullvalue';
                break;
            case 'xsd:string':
                if ($value == 'NULL')
                    $this->m_Value = 'nullvalue';
                break;
            case 'xsd:boolean':
                if ($value === 'false')
                    $this->m_Value = false;
                elseif ($value === 'true')
                    $this->m_Value = true;
                else
                    $this->m_Value = (bool) $value;
                break;
        }
    }
    function SetSafeValue($value)
    {
        $value = stripslashes(HTMLSpecialChars($value));
        $this->m_Value = preg_replace("/\r\n|\n|\r/", '<br />', $value);
    }
    function ResetValue()
    {
        $this->m_Value = 'nullvalue';
    }
    function SetXsdType($value)
    {
        $this->m_XsdType = $value;
    }
    function GetXsdType()
    {
        return $this->m_XsdType;
    }
    function GetXsdListType()
    {
        return $this->m_XsdType . 'List';
    }
    function SetXsdElement($value)
    {
        $this->m_XsdElement = $value;
    }
    function GetXsdElement()
    {
        return $this->m_XsdElement;
    }
    function GetXsdListElement()
    {
        return $this->m_XsdElement . 'List';
    }
    function IsUpdate()
    {
        return $this->m_Update;
    }
    function IsNull()
    {
        return (!isset($this->m_Value) || $this->m_Value === 'nullvalue');
    }
    function IsEmpty()
    {
        return ($this->m_Value === '');
    }
    function IsEmptyNull()
    {
        return (!isset($this->m_Value) || ($this->m_Value === '' || $this->m_Value === 'nullvalue'));
    }
    function IsValid()
    {
        return true;
    }
    function IsStream()
    {
        return $this->m_Stream;
    }
    function Equals($what)
    {
        return ($this->m_Value === $what);
    }
    function EqualsIgnoreCase($what)
    {
        return (strcasecmp($this->m_Value, $what) === 0);
    }
    function EqualsVT($whatVT)
    {
        return ($this->m_Value == $whatVT->GetValue());
    }
    function ValueEcho()
    {
        if (!$this->IsNull())
            return addslashes(html_entity_decode($this->m_Value));
        else
            return '';
    }
    function SafeEcho()
    {
        if (!$this->IsNull())
            return htmlentities($this->m_Value);
        else
            return '';
    }
    function EchoLocalized()
    {
        $locale = '';
        if (!$this->IsNull())
            eval("\$locale = $this->m_Value;");
        return $locale;
    }
    function StartsWith($what)
    {
        return (!$this->IsNull() && preg_match("/^$what/", $this->m_Value));
    }
    function EndsWith($what)
    {
        return (!$this->IsNull() && preg_match("/$what$/", $this->m_Value));
    }
    function IsGreaterThanDateValue($what)
    {
        if ($this->IsEmptyNull())
            return true;
        return ($this->GetTimeValue() > $what);
    }
    function IsEqualToDateValue($what)
    {
        return ($this->GetTimeValue() == $what);
    }
    function IsLessThanDateValue($what)
    {
        if ($this->IsEmptyNull())
            return false;
        return ($this->GetTimeValue() < $what);
    }
    function Matches($what)
    {
        return (!$this->IsNull() && preg_match("/$what/i", $this->m_Value));
    }
    function DefaultData()
    {
        
    }
}
/* * ************** */
class DataType
{
    var $m_ColumnName;
    var $m_Value;
    var $m_PrimaryKey;
    var $m_TableName;
    var $m_TableAlias;
    var $m_Active;
    var $m_HasDefault;
    var $m_AutoTimestamp;
    public function __construct($dataSet, $columnName, $value, $primaryKey, $hasDefault = false, $autoTimestamp = false)
    {
        $this->m_ColumnName = $columnName;
        $this->m_Value = new ValueType('', '');
        if (!empty($value))
            $this->m_Value->SetValue($value);
        $this->m_PrimaryKey = $primaryKey;
        $this->m_HasDefault = $hasDefault;
        $this->m_TableName = $dataSet->m_TableName;
        $this->m_TableAlias = $dataSet->m_TableAlias;
        $this->m_Active = true;
        $this->m_AutoTimestamp = $autoTimestamp;
    }
    function Set($valueType)
    {
        if (!($valueType->IsNull() && $this->m_HasDefault))
            $this->m_Value = $valueType;
    }
    function SetColumnName($value)
    {
        $this->m_ColumnName = $value;
    }
    function GetColumnName($modifier = '')
    {
        return $this->m_ColumnName;
    }
    function SetValue($value)
    {
        $this->m_Value->SetValue($value);
    }
    function SetSafeValue($value)
    {
        $this->m_Value->SetSafeValue($value);
    }
    function GetValue()
    {
        return $this->m_Value->GetValue();
    }
    function GetSafeValue()
    {
        return $this->m_Value->GetSafeValue();
    }
    function SetPrimaryKey($value)
    {
        $this->m_PrimaryKey = $value;
    }
    function GetPrimaryKey()
    {
        return $this->m_PrimaryKey;
    }
    function SetTableName($value)
    {
        $this->m_TableName = $value;
    }
    function GetTableName()
    {
        return $this->m_TableName;
    }
    function SetTableAlias($value)
    {
        $this->m_TableAlias = $value;
    }
    function GetTableAlias()
    {
        return $this->m_TableAlias;
    }
    function NameAlias()
    {
        return $this->m_TableAlias . '.' . $this->m_ColumnName;
    }
    function IsPrimaryKey()
    {
        return $this->m_PrimaryKey;
    }
    function IsEmpty()
    {
        if ($this->m_Value->IsEmptyNull())
            return true;
        return false;
    }
    function IsValid()
    {
        return ($this->m_Value->IsValid() && !($this->m_Value->IsNull() && $this->m_HasDefault));
    }
    function Activate()
    {
        $this->m_Active = true;
    }
    function Deactivate()
    {
        $this->m_Active = false;
    }
    function IsActive()
    {
        return $this->m_Active;
    }
    function IsUpdate()
    {
        return $this->m_Value->IsUpdate();
    }
    function FormatForDBInsert()
    {
        if ($this->m_AutoTimestamp || $this->m_Value->Equals('now()'))
            return 'now()';
        if ($this->m_Value->IsEmpty())
            return "''";
        elseif ($this->m_Value->IsNull() || $this->m_Value->Equals('null') || $this->m_Value->Equals('n/a'))
            return 'NULL';

        if ($this->m_Value->m_XsdType == 'xsd:boolean')
            return ($this->GetValue() ? '1' : '0');

        $str = preg_replace('/href=.*javascript.*[\'"]/', 'href="#"', $this->m_Value->GetValue());
        $str = str_replace(array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6", "&#8211;", "&#8212;", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8230;", "\\'"), array("'", "'", '"', '"', '-', '-', '...', '-', '-', "'", "'", '"', '"', '...', "'"), $str);
        return "'" . str_replace("'", "''", $str) . "'";
    }
    function InsertSQL()
    {
        return $this->FormatForDBInsert();
    }
}