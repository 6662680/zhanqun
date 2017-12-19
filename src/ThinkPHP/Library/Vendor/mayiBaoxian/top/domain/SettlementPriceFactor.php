<?php

/**
 * 计价因子，填写规则：1、有计价因子场景：{name:计价因子名称 ,value:数量｝如示例；2、没有计价因子场景：填默认值：｛name:计价因子,value:0｝
 * @author auto create
 */
class SettlementPriceFactor
{
	
	/** 
	 * 计价因子说明
	 **/
	public $desc;
	
	/** 
	 * 计价因子属性
	 **/
	public $name;
	
	/** 
	 * 计价因子实际值
	 **/
	public $value;	
}
?>