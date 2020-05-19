<?php
include_once 'classes/dataset/BaseData.php';
class IFDUser extends IFD
{
    var $m_Id;
    var $m_IpAddress;
    var $m_CreateDate;
    public function __construct()
    {
        parent::__construct();

        $this->SetIFDName('IFDUser', 'tns:User', 'User');

        $this->m_Id = new ValueType('xsd:unsignedInt', 'Id');
        $this->m_IpAddress = new ValueType('xsd:string', 'IpAddress');
        $this->m_CreateDate = new ValueType('xsd:dateTime', 'CreateDate');
    }
    function Copy(IFDUser $copyFrom)
    {
        if ($this == $copyFrom)
            return;
        $this->m_Id->SetValue($copyFrom->m_Id->GetValue());
        $this->m_IpAddress->SetValue($copyFrom->m_IpAddress->GetValue());
        $this->m_CreateDate->SetValue($copyFrom->m_CreateDate->GetValue());
    }
    function Set(IFDUser $setFrom)
    {
        if ($this == $setFrom)
            return;
        if (!$setFrom->m_Id->IsNull())
            $this->m_Id->SetValue($setFrom->m_Id->GetValue());
        if (!$setFrom->m_IpAddress->IsNull())
            $this->m_IpAddress->SetValue($setFrom->m_IpAddress->GetValue());
        if (!$setFrom->m_CreateDate->IsNull())
            $this->m_CreateDate->SetValue($setFrom->m_CreateDate->GetValue());
    }
    function IsEmpty()
    {
        if ($this->m_Id->IsNull() &&
          $this->m_IpAddress->IsNull() &&
          $this->m_CreateDate->IsNull())
            return true;
        return false;
    }
}
class DSUser extends DataSet
{
    public function __construct($tableAlias = 'usr')
    {
        parent::__construct();

        $this->m_TableName = 'user';
        $this->m_TableAlias = $tableAlias;
        $this->m_Columns = array('id' => new DataType($this, 'id', '', true),
          'ipAddress' => new DataType($this, 'ip_address', '', false),
          'createDate' => new DataType($this, 'create_date', 'now()', false, true)
        );
    }
    function Set(IFDUser $ifdUser)
    {
        $this->m_Columns['id']->Set($ifdUser->m_Id);
        $this->m_Columns['ipAddress']->Set($ifdUser->m_IpAddress);
        $this->m_Columns['createDate']->Set($ifdUser->m_CreateDate);
    }
    function Populate(IFDUser &$ifdUser)
    {
        $ifdUser->m_Id->SetValue($this->m_Columns['id']->GetValue());
        $ifdUser->m_IpAddress->SetValue($this->m_Columns['ipAddress']->GetValue());
        $ifdUser->m_CreateDate->SetValue($this->m_Columns['createDate']->GetValue());
    }
    function LoadByUser($ifdUser, $count = false)
    {
        $this->m_WhereClause = '';
        if (!$ifdUser->m_Id->IsEmptyNull())
            $this->m_WhereClause = DBAND . $this->m_Columns['id']->NameAlias() . DBEQUALS . $ifdUser->m_Id->GetValue();
        if (!$ifdUser->m_IpAddress->IsEmptyNull())
            $this->m_WhereClause .= DBAND . $this->Comparison($this->m_Columns['ipAddress']->NameAlias(), $ifdUser->m_IpAddress->GetValue());
        if ($this->m_WhereClause != '')
            $this->m_WhereClause = DBWHERE . preg_replace('/^\s*' . DBAND . '/', '', $this->m_WhereClause);
    }
}