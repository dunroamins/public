<?php
include_once 'classes/dataset/BaseData.php';
class IFDPollVote extends IFD
{
    var $m_Id;
    var $m_UserId;
    var $m_PollAnswerId;
    var $m_CreateDate;
    public function __construct()
    {
        parent::__construct();

        $this->SetIFDName('IFDPollVote', 'tns:PollVote', 'PollVote');

        $this->m_Id = new ValueType('xsd:unsignedInt', 'Id');
        $this->m_UserId = new ValueType('xsd:unsignedInt', 'UserId');
        $this->m_PollAnswerId = new ValueType('xsd:unsignedInt', 'PollAnswerId');
        $this->m_CreateDate = new ValueType('xsd:dateTime', 'CreateDate');
    }
    function Copy(IFDPollVote $copyFrom)
    {
        if ($this == $copyFrom)
            return;
        $this->m_Id->SetValue($copyFrom->m_Id->GetValue());
        $this->m_UserId->SetValue($copyFrom->m_UserId->GetValue());
        $this->m_PollAnswerId->SetValue($copyFrom->m_PollAnswerId->GetValue());
        $this->m_CreateDate->SetValue($copyFrom->m_CreateDate->GetValue());
    }
    function Set(IFDPollVote $setFrom)
    {
        if ($this == $setFrom)
            return;
        if (!$setFrom->m_Id->IsNull())
            $this->m_Id->SetValue($setFrom->m_Id->GetValue());
        if (!$setFrom->m_UserId->IsNull())
            $this->m_UserId->SetValue($setFrom->m_UserId->GetValue());
        if (!$setFrom->m_PollAnswerId->IsNull())
            $this->m_PollAnswerId->SetValue($setFrom->m_PollAnswerId->GetValue());
        if (!$setFrom->m_CreateDate->IsNull())
            $this->m_CreateDate->SetValue($setFrom->m_CreateDate->GetValue());
    }
    function IsEmpty()
    {
        if ($this->m_Id->IsNull() &&
          $this->m_UserId->IsNull() &&
          $this->m_PollAnswerId->IsNull() &&
          $this->m_CreateDate->IsNull())
            return true;
        return false;
    }
}
class DSPollVote extends DataSet
{
    public function __construct($tableAlias = 'pvo')
    {
        parent::__construct();

        $this->m_TableName = 'poll_vote';
        $this->m_TableAlias = $tableAlias;
        $this->m_Columns = array('id' => new DataType($this, 'id', '', true),
          'userId' => new DataType($this, 'user_id', '', false),
          'pollAnswerId' => new DataType($this, 'poll_answer_id', '', false),
          'createDate' => new DataType($this, 'create_date', 'now()', false, true)
        );
    }
    function Set(IFDPollVote $ifdPollVote)
    {
        $this->m_Columns['id']->Set($ifdPollVote->m_Id);
        $this->m_Columns['userId']->Set($ifdPollVote->m_UserId);
        $this->m_Columns['pollAnswerId']->Set($ifdPollVote->m_PollAnswerId);
        $this->m_Columns['createDate']->Set($ifdPollVote->m_CreateDate);
    }
    function Populate(IFDPollVote &$ifdPollVote)
    {
        $ifdPollVote->m_Id->SetValue($this->m_Columns['id']->GetValue());
        $ifdPollVote->m_UserId->SetValue($this->m_Columns['userId']->GetValue());
        $ifdPollVote->m_PollAnswerId->SetValue($this->m_Columns['pollAnswerId']->GetValue());
        $ifdPollVote->m_CreateDate->SetValue($this->m_Columns['createDate']->GetValue());
    }
    function LoadByPollVote($ifdPollVote, $count = false)
    {
        $this->m_WhereClause = '';
        if (!$ifdPollVote->m_Id->IsEmptyNull())
            $this->m_WhereClause = DBAND . $this->m_Columns['id']->NameAlias() . DBEQUALS . $ifdPollVote->m_Id->GetValue();
        if (!$ifdPollVote->m_UserId->IsEmptyNull())
            $this->m_WhereClause .= DBAND . $this->m_Columns['userId']->NameAlias() . DBEQUALS . $ifdPollVote->m_UserId->GetValue();
        if (!$ifdPollVote->m_PollAnswerId->IsEmptyNull())
            $this->m_WhereClause .= DBAND . $this->m_Columns['pollAnswerId']->NameAlias() . DBEQUALS . $ifdPollVote->m_PollAnswerId->GetValue();
        if ($this->m_WhereClause != '')
            $this->m_WhereClause = DBWHERE . preg_replace('/^\s*' . DBAND . '/', '', $this->m_WhereClause);
    }
}