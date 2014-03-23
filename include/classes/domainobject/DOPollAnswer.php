<?php
include_once 'classes/domainobject/BaseDO.php';
include_once 'classes/dataset/PollAnswerData.php';
class DOPollAnswer extends DomainObject
{
	public function __construct()
	{
		parent::__construct();
		$this->NewIFD();
	}
	function NewDataSet()
	{
		if (!isset($this->m_DataSet))
			$this->m_DataSet = new DSPollAnswer();
	}
	function NewIFD()
	{
		$this->m_IFDWorking = new IFDPollAnswer();
	}
	function Count()
	{
		$this->NewDataSet();
		$this->m_DataSet->LoadByPollAnswer($this->m_IFDWorking, true);
		$count = $this->m_DataSet->SelectCount();
		unset($this->m_DataSet);
		return $count;
	}
}
class DOPollAnswerList extends DomainObjectList
{
	public function __construct()
	{
		parent::__construct();
	}
	function NewDataSet()
	{
		if (!isset($this->m_DataSet))
			$this->m_DataSet = new DSPollAnswer();
	}
	function Populate()
	{
		$ctr = 0;
		$this->m_DOList = array();
		do
		{
			$ifd = new IFDPollAnswer();
			$do = new DOPollAnswer();
			$this->m_DataSet->Populate($ifd);
			$do->Set($ifd);
			$this->m_DOList[] = $do;
		}
		while ($this->m_DataSet->Fetch(++$ctr));
		unset($ifd);
		unset($do);
	}
	function LoadByPollAnswer($user)
	{
		$this->NewDataSet();
		$this->m_DataSet->LoadByPollAnswer($user, false);
		return $this->ExecuteLoad();
	}
}