<?php
include_once 'classes/domainobject/BaseDO.php';
include_once 'classes/dataset/PollQuestionData.php';
class DOPollQuestion extends DomainObject
{
	public function __construct()
	{
		parent::__construct();
		$this->NewIFD();
	}
	function NewDataSet()
	{
		if (!isset($this->m_DataSet))
			$this->m_DataSet = new DSPollQuestion();
	}
	function NewIFD()
	{
		$this->m_IFDWorking = new IFDPollQuestion();
	}
	function Count()
	{
		$this->NewDataSet();
		$this->m_DataSet->LoadByPollQuestion($this->m_IFDWorking, true);
		$count = $this->m_DataSet->SelectCount();
		unset($this->m_DataSet);
		return $count;
	}
}
class DOPollQuestionList extends DomainObjectList
{
	public function __construct()
	{
		parent::__construct();
	}
	function NewDataSet()
	{
		if (!isset($this->m_DataSet))
			$this->m_DataSet = new DSPollQuestion();
	}
	function Populate()
	{
		$ctr = 0;
		$this->m_DOList = array();
		do
		{
			$ifd = new IFDPollQuestion();
			$do = new DOPollQuestion();
			$this->m_DataSet->Populate($ifd);
			$do->Set($ifd);
			$this->m_DOList[] = $do;
		}
		while ($this->m_DataSet->Fetch(++$ctr));
		unset($ifd);
		unset($do);
	}
	function LoadByPollQuestion($user)
	{
		$this->NewDataSet();
		$this->m_DataSet->LoadByPollQuestion($user, false);
		return $this->ExecuteLoad();
	}
}