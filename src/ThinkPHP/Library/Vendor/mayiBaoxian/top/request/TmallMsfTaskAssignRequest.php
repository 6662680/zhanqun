<?php
/**
 * TOP API: tmall.msf.task.assign request
 * 
 * @author auto create
 * @since 1.0, 2016.12.06
 */
class TmallMsfTaskAssignRequest
{
	/** 
	 * 派单参数
	 **/
	private $reservationDto;
	
	private $apiParas = array();
	
	public function setReservationDto($reservationDto)
	{
		$this->reservationDto = $reservationDto;
		$this->apiParas["reservation_dto"] = $reservationDto;
	}

	public function getReservationDto()
	{
		return $this->reservationDto;
	}

	public function getApiMethodName()
	{
		return "tmall.msf.task.assign";
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
