<?php
/**
 * TOP API: tmall.servicecenter.tasks.search request
 * 
 * @author auto create
 * @since 1.0, 2017.04.17
 */
class TmallServicecenterTasksSearchRequest
{
	/** 
	 * 结束时间:  开始时间和结束时间不能超过15分钟
	 **/
	private $end;
	
	/** 
	 * 开始时间:  开始时间和结束时间不能超过15分钟
	 **/
	private $start;
	
	private $apiParas = array();
	
	public function setEnd($end)
	{
		$this->end = $end;
		$this->apiParas["end"] = $end;
	}

	public function getEnd()
	{
		return $this->end;
	}

	public function setStart($start)
	{
		$this->start = $start;
		$this->apiParas["start"] = $start;
	}

	public function getStart()
	{
		return $this->start;
	}

	public function getApiMethodName()
	{
		return "tmall.servicecenter.tasks.search";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->end,"end");
		RequestCheckUtil::checkNotNull($this->start,"start");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
