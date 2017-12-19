<?php
/**
 * TOP API: tmall.sic.salesarea.update request
 * 
 * @author auto create
 * @since 1.0, 2016.03.05
 */
class TmallSicSalesareaUpdateRequest
{
	/** 
	 * 商品区域和价格的对应关系，采用json方式传递
	 **/
	private $areaPriceStr;
	
	/** 
	 * 前台商品id
	 **/
	private $numIid;
	
	/** 
	 * 商品对应的地域信息，采用json传递
	 **/
	private $saleareaStr;
	
	/** 
	 * 商品sku的json字符串
	 **/
	private $skuIdStr;
	
	private $apiParas = array();
	
	public function setAreaPriceStr($areaPriceStr)
	{
		$this->areaPriceStr = $areaPriceStr;
		$this->apiParas["area_price_str"] = $areaPriceStr;
	}

	public function getAreaPriceStr()
	{
		return $this->areaPriceStr;
	}

	public function setNumIid($numIid)
	{
		$this->numIid = $numIid;
		$this->apiParas["num_iid"] = $numIid;
	}

	public function getNumIid()
	{
		return $this->numIid;
	}

	public function setSaleareaStr($saleareaStr)
	{
		$this->saleareaStr = $saleareaStr;
		$this->apiParas["salearea_str"] = $saleareaStr;
	}

	public function getSaleareaStr()
	{
		return $this->saleareaStr;
	}

	public function setSkuIdStr($skuIdStr)
	{
		$this->skuIdStr = $skuIdStr;
		$this->apiParas["sku_id_str"] = $skuIdStr;
	}

	public function getSkuIdStr()
	{
		return $this->skuIdStr;
	}

	public function getApiMethodName()
	{
		return "tmall.sic.salesarea.update";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->areaPriceStr,"areaPriceStr");
		RequestCheckUtil::checkNotNull($this->numIid,"numIid");
		RequestCheckUtil::checkNotNull($this->saleareaStr,"saleareaStr");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
