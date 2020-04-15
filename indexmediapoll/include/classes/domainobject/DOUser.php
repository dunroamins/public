<?php
include_once 'classes/domainobject/BaseDO.php';
include_once 'classes/dataset/UserData.php';
class DOUser extends DomainObject
{
	public function __construct()
	{
		parent::__construct();
		$this->NewIFD();
	}
	function NewDataSet()
	{
		if (!isset($this->m_DataSet))
			$this->m_DataSet = new DSUser();
	}
	function NewIFD()
	{
		$this->m_IFDWorking = new IFDUser();
	}
	function Count()
	{
		$this->NewDataSet();
		$this->m_DataSet->LoadByUser($this->m_IFDWorking, true);
		$count = $this->m_DataSet->SelectCount();
		unset($this->m_DataSet);
		return $count;
	}
}
class DOUserList extends DomainObjectList
{
	public function __construct()
	{
		parent::__construct();
	}
	function NewDataSet()
	{
		if (!isset($this->m_DataSet))
			$this->m_DataSet = new DSUser();
	}
	function Populate()
	{
		$ctr = 0;
		$this->m_DOList = array();
		do
		{
			$ifd = new IFDUser();
			$do = new DOUser();
			$this->m_DataSet->Populate($ifd);
			$do->Set($ifd);
			$this->m_DOList[] = $do;
		}
		while ($this->m_DataSet->Fetch(++$ctr));
		unset($ifd);
		unset($do);
	}
	function LoadByUser($user)
	{
		$this->NewDataSet();
		$this->m_DataSet->LoadByUser($user, false);
		return $this->ExecuteLoad();
	}
}