<?php
include_once 'classes/domainobject/BaseDO.php';
include_once 'classes/dataset/PollVoteData.php';
class DOPollVote extends DomainObject
{
	public function __construct()
	{
		parent::__construct();
		$this->NewIFD();
	}
	function NewDataSet()
	{
		if (!isset($this->m_DataSet))
			$this->m_DataSet = new DSPollVote();
	}
	function NewIFD()
	{
		$this->m_IFDWorking = new IFDPollVote();
	}
	function Count()
	{
		$this->NewDataSet();
		$this->m_DataSet->LoadByPollVote($this->m_IFDWorking, true);
		$count = $this->m_DataSet->SelectCount();
		unset($this->m_DataSet);
		return $count;
	}
}
class DOPollVoteList extends DomainObjectList
{
	public function __construct()
	{
		parent::__construct();
	}
	function NewDataSet()
	{
		if (!isset($this->m_DataSet))
			$this->m_DataSet = new DSPollVote();
	}
	function Populate()
	{
		$ctr = 0;
		$this->m_DOList = array();
		do
		{
			$ifd = new IFDPollVote();
			$do = new DOPollVote();
			$this->m_DataSet->Populate($ifd);
			$do->Set($ifd);
			$this->m_DOList[] = $do;
		}
		while ($this->m_DataSet->Fetch(++$ctr));
		unset($ifd);
		unset($do);
	}
	function LoadByPollVote($user)
	{
		$this->NewDataSet();
		$this->m_DataSet->LoadByPollVote($user, false);
		return $this->ExecuteLoad();
	}
}