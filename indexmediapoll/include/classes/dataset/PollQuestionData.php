<?php
include_once 'classes/dataset/BaseData.php';
class IFDPollQuestion extends IFD
{
    var $m_Id;
    var $m_Title;
    public function __construct()
    {
        parent::__construct();

        $this->SetIFDName('IFDPollQuestion', 'tns:PollQuestion', 'PollQuestion');

        $this->m_Id = new ValueType('xsd:unsignedInt', 'Id');
        $this->m_Title = new ValueType('xsd:string', 'Title');
    }
    function Copy(IFDPollQuestion $copyFrom)
    {
        if ($this == $copyFrom)
            return;
        $this->m_Id->SetValue($copyFrom->m_Id->GetValue());
        $this->m_Title->SetValue($copyFrom->m_Title->GetValue());
    }
    function Set(IFDPollQuestion $setFrom)
    {
        if ($this == $setFrom)
            return;
        if (!$setFrom->m_Id->IsNull())
            $this->m_Id->SetValue($setFrom->m_Id->GetValue());
        if (!$setFrom->m_Title->IsNull())
            $this->m_Title->SetValue($setFrom->m_Title->GetValue());
    }
    function IsEmpty()
    {
        if ($this->m_Id->IsNull() &&
          $this->m_Title->IsNull())
            return true;
        return false;
    }
}
class DSPollQuestion extends DataSet
{
    public function __construct($tableAlias = 'pqu')
    {
        parent::__construct();

        $this->m_TableName = 'poll_question';
        $this->m_TableAlias = $tableAlias;
        $this->m_Columns = array('id' => new DataType($this, 'id', '', true),
          'title' => new DataType($this, 'title', '', false)
        );
    }
    function Set(IFDPollQuestion $ifdPollQuestion)
    {
        $this->m_Columns['id']->Set($ifdPollQuestion->m_Id);
        $this->m_Columns['title']->Set($ifdPollQuestion->m_Title);
    }
    function Populate(IFDPollQuestion &$ifdPollQuestion)
    {
        $ifdPollQuestion->m_Id->SetValue($this->m_Columns['id']->GetValue());
        $ifdPollQuestion->m_Title->SetValue($this->m_Columns['title']->GetValue());
    }
    function LoadByPollQuestion($ifdPollQuestion, $count = false)
    {
        $this->m_WhereClause = '';
        if (!$ifdPollQuestion->m_Id->IsEmptyNull())
            $this->m_WhereClause = DBAND . $this->m_Columns['id']->NameAlias() . DBEQUALS . $ifdPollQuestion->m_Id->GetValue();
        if (!$ifdPollQuestion->m_Title->IsEmptyNull())
            $this->m_WhereClause .= DBAND . $this->Comparison($this->m_Columns['title']->NameAlias(), $ifdPollQuestion->m_Title->GetValue());
        if ($this->m_WhereClause != '')
            $this->m_WhereClause = DBWHERE . preg_replace('/^\s*' . DBAND . '/', '', $this->m_WhereClause);
    }
}