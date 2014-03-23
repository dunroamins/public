<?php
class DomainObject
{
    protected $m_IFDWorking;
    private $m_MarkForDeletion = false;
    protected $m_DataSet;
    public function __construct()
    {
        
    }
    public function __destruct()
    {
        foreach ($this as $index => $value)
            unset($this->$index);
    }
    protected function NewDataSet() // must be overridden in the derived class
    {
        
    }
    protected function NewIFD() // must be overridden in the derived class
    {
        
    }
    public function Get()
    {
        return $this->m_IFDWorking;
    }
    public function Set(&$value)
    {
        $this->m_IFDWorking = $value;
    }
    public function Save()
    {
        $result = false;
        $this->NewDataSet();
        $this->m_DataSet->Set($this->m_IFDWorking);
        if ($this->m_MarkForDeletion == true)
            $result = $this->m_DataSet->Delete();
        elseif ($this->m_DataSet->IsPrimaryKeySet())
            $result = $this->m_DataSet->Update();
        else
        {
            if ($this->m_DataSet->Insert() == true)
            {
                $this->m_DataSet->SetPrimaryKey();
                $this->m_DataSet->Populate($this->m_IFDWorking);
                $result = true;
            }
        }
        unset($this->m_DataSet);
        return $result;
    }
    protected function ExecuteLoad()
    {
        $this->NewIFD();
        $result = $this->m_DataSet->Select();
        if ($result)
            $this->m_DataSet->Populate($this->m_IFDWorking);
        unset($this->m_DataSet);
        return $result;
    }
    public function MarkForDeletion()
    {
        $this->m_MarkForDeletion = true;
    }
    public function GetGroupAssociationIds()
    {
        return $this->m_IFDWorking->EvaluateNotificationGroups();
    }
    public function GetGroupAssociationIdsByGroup($group)
    {
        return $this->m_IFDWorking->EvaluateNotificationGroup($group);
    }
    public function ReturnIFDByPrimaryKey($key)
    {
        $this->NewDataSet();
        $this->m_DataSet->LoadByPrimaryKey($key);
        $this->ExecuteLoad();
        return $this->m_IFDWorking;
    }
}
class DomainObjectList
{
    var $m_DOList = array();
    var $m_DataSet;
    var $m_RowOffset = 0;
    var $m_ExecuteLoad = true;
    var $m_MarkForDeletion = false;
    var $m_MarkForUpdate = false;
    var $m_MarkForCount = false;
    var $m_IFD;
    var $m_RowLimitReached = true;
    public function __construct()
    {
        $this->Reset();
    }
    public function __destruct()
    {
        foreach ($this as $index => $value)
        {
            if (is_object($this->$index))
                $this->$index->__destruct();
            unset($this->$index);
        }
    }
    function Get()
    {
        return $this->m_DOList;
    }
    function GetIFDList()
    {
        $ifdList = array();
        foreach ($this->m_DOList as $do)
            $ifdList[] = $do->Get();
        return $ifdList;
    }
    function Set($value)
    {
        $this->m_DOList = $value;
    }
    function SetForMassUpdate($ifd)
    {
        $this->m_IFD = $ifd;
        $this->m_MarkForUpdate = true;
        $this->m_ExecuteLoad = false;
    }
    function SetForMassDeletion()
    {
        $this->m_MarkForDeletion = true;
        $this->m_ExecuteLoad = false;
    }
    function SetForCount()
    {
        $this->m_MarkForCount = true;
    }
    function Reset()
    {
        $this->m_RowOffset = 0;
        $this->m_RowLimitReached = true;
    }
    function Load()
    {
        $this->NewDataSet();
        $this->m_DataSet->Load();
        return $this->ExecuteLoad();
    }
    function Save()
    {
        $result = true;
        if ($this->m_MarkForDeletion)
        {
            if (!isset($this->m_DataSet))
                $result = false;
            else
                $result = $this->m_DataSet->Delete(true);
        }
        else if ($this->m_MarkForUpdate)
        {
            if (!isset($this->m_DataSet))
                $result = false;
            else
            {
                $this->m_DataSet->Set($this->m_IFD);
                $result = $this->m_DataSet->Update(true);
            }
        }
        else
        {
            foreach ($this->m_DOList as $do)
                $result = $result && $do->Save();
        }
        unset($this->m_DataSet);
        $this->m_IFD = null;
        $this->m_MarkForUpdate = false;
        $this->m_MarkForDeletion = false;
        $this->m_ExecuteLoad = true;
        return $result;
    }
    function Append($list)
    {
        $this->m_DOList = array_merge($this->m_DOList, $list);
    }
    function MarkForDeletion()
    {
        if (!$this->m_ExecuteLoad)
            $this->m_MarkForDeletion = true;
        else
        {
            for ($i = 0, $imax = sizeof($this->m_DOList); $i < $imax; ++$i)
                $this->m_DOList[$i]->MarkForDeletion();
        }
    }
    function ExecuteLoad()
    {
        $result = false;
        if (!$this->m_ExecuteLoad)
            $result = $this->Save();
        else
        {
            if ($this->m_MarkForCount)
                $result = $this->m_DataSet->SelectCount();
            elseif ($this->m_RowLimitReached)
            {
                $result = $this->m_DataSet->Select($this->m_RowOffset);
                if ($result)
                    $this->Populate();
                $this->m_RowLimitReached = (count($this->m_DOList) >= DB_MAX_ROW);
                $this->m_RowOffset += DB_MAX_ROW;
            }
        }
        unset($this->m_DataSet);
        return $result;
    }
}