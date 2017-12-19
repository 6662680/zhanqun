<?php
/**
 * TOP API: tmall.msf.reservation request
 * 
 * @author auto create
 * @since 1.0, 2016.03.21
 */
class TmallMsfReservationRequest
{
	/** 
	 * 预约内容
	 **/
	private $reservInfo;
	
	private $apiParas = array();
	
	public function setReservInfo($reservInfo)
	{
		$this->reservInfo = $reservInfo;
		$this->apiParas["reserv_info"] = $reservInfo;
	}

	public function getReservInfo()
	{
		return $this->reservInfo;
	}

	public function getApiMethodName()
	{
		return "tmall.msf.reservation";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
