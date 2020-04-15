<?php
include_once 'classes/dataset/BaseData.php';
include_once 'classes/dataset/PollQuestionData.php';
class IFDPollAnswer extends IFD
{
    var $m_Id;
    var $m_Title;
    var $m_PollQuestionId;
    var $m_IFDPollQuestion;
    public function __construct()
    {
        parent::__construct();

        $this->SetIFDName('IFDPollAnswer', 'tns:PollAnswer', 'PollAnswer');

        $this->m_Id = new ValueType('xsd:unsignedInt', 'Id');
        $this->m_Title = new ValueType('xsd:string', 'Title');
        $this->m_PollQuestionId = new ValueType('xsd:unsignedInt', 'PollQuestionId');
        $this->m_IFDPollQuestion = new IFDPollQuestion();
    }
    function Copy(IFDPollAnswer $copyFrom)
    {
        if ($this == $copyFrom)
            return;
        $this->m_Id->SetValue($copyFrom->m_Id->GetValue());
        $this->m_Title->SetValue($copyFrom->m_Title->GetValue());
        $this->m_PollQuestionId->SetValue($copyFrom->m_PollQuestionId->GetValue());
        $this->m_IFDPollQuestion->Copy($copyFrom->m_IFDPollQuestion);
    }
    function Set(IFDPollAnswer $setFrom)
    {
        if ($this == $setFrom)
            return;
        if (!$setFrom->m_Id->IsNull())
            $this->m_Id->SetValue($setFrom->m_Id->GetValue());
        if (!$setFrom->m_Title->IsNull())
            $this->m_Title->SetValue($setFrom->m_Title->GetValue());
        if (!$setFrom->m_PollQuestionId->IsNull())
            $this->m_PollQuestionId->SetValue($setFrom->m_PollQuestionId->GetValue());
        $this->m_IFDPollQuestion->Set($copyFrom->m_IFDPollQuestion);
    }
    function IsEmpty()
    {
        if ($this->m_Id->IsNull() &&
          $this->m_Title->IsNull() &&
          $this->m_PollQuestionId->IsNull())
            return true;
        return false;
    }
}
class DSPollAnswer extends DataSet
{
    public function __construct($tableAlias = 'pan')
    {
        parent::__construct();

        $this->m_TableName = 'poll_answer';
        $this->m_TableAlias = $tableAlias;
        $this->m_Columns = array('id' => new DataType($this, 'id', '', true),
          'title' => new DataType($this, 'title', '', false),
          'pollQuestionId' => new DataType($this, 'poll_question_id', '', false)
        );

        $dsPollQuestion = new DSPollQuestion();
        $dsPollQuestion->IncludeColumnsAll();
        $dsPollQuestion->AddJoinedColumn($this->m_Columns['pollQuestionId']->NameAlias(), $dsPollQuestion->m_Columns['id']->NameAlias());
        $this->JoinTables($this, $dsPollQuestion);
    }
    function Set(IFDPollAnswer $ifdPollAnswer)
    {
        $this->m_Columns['id']->Set($ifdPollAnswer->m_Id);
        $this->m_Columns['title']->Set($ifdPollAnswer->m_Title);
        $this->m_Columns['pollQuestionId']->Set($ifdPollAnswer->m_PollQuestionId);
    }
    function Populate(IFDPollAnswer &$ifdPollAnswer)
    {
        $ifdPollAnswer->m_Id->SetValue($this->m_Columns['id']->GetValue());
        $ifdPollAnswer->m_Title->SetValue($this->m_Columns['title']->GetValue());
        $ifdPollAnswer->m_PollQuestionId->SetValue($this->m_Columns['pollQuestionId']->GetValue());

        $dsPollQuestion = $this->GetDataSet('DSPollQuestion');
        if ($dsPollQuestion != null)
            $dsPollQuestion->Populate($ifdPollAnswer->m_IFDPollQuestion);
        unset($dsPollQuestion);
    }
    function LoadByPollAnswer($ifdPollAnswer, $count = false)
    {
        $this->m_WhereClause = '';
        if (!$ifdPollAnswer->m_Id->IsEmptyNull())
            $this->m_WhereClause = DBAND . $this->m_Columns['id']->NameAlias() . DBEQUALS . $ifdPollAnswer->m_Id->GetValue();
        if (!$ifdPollAnswer->m_Title->IsEmptyNull())
            $this->m_WhereClause .= DBAND . $this->Comparison($this->m_Columns['title']->NameAlias(), $ifdPollAnswer->m_Title->GetValue());
        if (!$ifdPollAnswer->m_PollQuestionId->IsEmptyNull())
            $this->m_WhereClause .= DBAND . $this->m_Columns['pollQuestionId']->NameAlias() . DBEQUALS . $ifdPollAnswer->m_PollQuestionId->GetValue();
        if ($this->m_WhereClause != '')
            $this->m_WhereClause = DBWHERE . preg_replace('/^\s*' . DBAND . '/', '', $this->m_WhereClause);
    }
}