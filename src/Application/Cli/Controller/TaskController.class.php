<?php

// +------------------------------------------------------------------------------------------ 
// | Author: longDD <longdd_love@163.com> 
// +------------------------------------------------------------------------------------------ 
// | There is no true,no evil,no light,there is only power. 
// +------------------------------------------------------------------------------------------ 
// | Description: 定时任务 Dates: 2017-03-15
// +------------------------------------------------------------------------------------------ 

namespace Cli\Controller;

class TaskController
{
	/** 阿里大于短信 */
	private $note = null;

	/**
	* 执行定时任务 (每分钟执行一次)
	*
	* @return void
	*/
	public function run()
	{
		\Think\Log::write('开始定时任务', 'ERR');
		$this->autoCancelOrder();
		$this->dealNoteQueue();
	}

	/**
	* 自动取消未付款的预付订单
	*
	* @return void
	*/
	public function autoCancelOrder()
	{
		/** 查询当前时间点前10分钟到15分钟，状态为下单，未付款的订单 */
		$map = array();
		$map['create_time'] = array(array('elt', (time() - 600)), array('egt', (time() - 900)), 'and');
		$map['status'] = 1;
		$map['pay_type'] = 2;
		$map['type'] = 3;
		$map['is_clearing'] = 0;
		$orders = M('order')->where($map)->getField('id', true);

		if (!empty($orders)) {
			\Think\Log::write('开始取消预付单{' . json_encode($orders) . '}', 'ERR');
			/** 取消未付款订单并写入订单日志 */
			$map = array();
			$map['id'] = array('in', $orders);
			$data = array();
			$data['status'] = -1;

			$rst = M('order')->where($map)->save($data);

			if ($rst === false) {
				\Think\Log::write('自动取消预付单失败{' . json_encode($orders) . '}', 'ERR');
			} else {
				\Think\Log::write('自动取消预付单成功{' . json_encode($orders) . '}', 'ERR');

				$data = array();
				foreach ($orders as $key => $value) {
					$item = array();
					$item['order_id'] = $value;
					$item['time'] = time();
					$item['action'] = '用户未付款, 自动取消预付订单！';
					$data[] = $item;
				}

				M('order_log')->addAll($data);
			}
		} else {
			\Think\Log::write('没有需要取消的预付单', 'ERR');
		}
	}

	/**
	 * 处理短信队列
	 *
	 * @return void
	 */
	public function dealNoteQueue()
	{
		\Think\Log::write('开始处理短信队列', 'ERR');

		/** 初始化redis */
        $redis = new \Redis();
        $redis->connect(C('REDIS_HOST'), C('REDIS_PORT'));

/**         $data = array();
        $data['orderId'] = $orderInfo['orderId'];
        $data['orderNumber'] = $orderInfo['orderNumber'];
        尝试次数 3次
        $data['attempts'] = 3; */

        $count = $redis->lLen('noteQueue');

        if ($count > 0) {
        	$this->note = new \Vendor\aliNote\aliNote();

        	while ($count > 0) {
        		$item = $redis->rPop('noteQueue');
        		$orderInfo = json_decode($item, true);

        		if (!$this->sendNote($orderInfo)) {

        			if ($orderInfo['attempts'] > 0) {
        				/** 发送失败，重新尝试发送，写入队列 */
	        			$data = $orderInfo;
	        			$data['attempts']--;
	        			$redis->lPush('noteQueue', json_encode($data));
        			} else {
        				\Think\Log::write('多次尝试无法发送[' . json_encode($orderInfo) . ']', 'ERR');
        			}    		
        		} else {
        			\Think\Log::write('短信发送成功[' . json_encode($orderInfo) . ']', 'ERR');
        		}

        		$count--;
        	}
        } else {
        	\Think\Log::write('短信队列为空，没有需要处理的任务', 'ERR');
        }

        $redis->close();
	}

	/**
	 * 发送短信
	 *
	 * @return void
	 */
	private function sendNote($orderInfo)
	{
		$map = array();
		$map['o.id'] = $orderInfo['orderId'];

		$item = M('order')->join('o left join `organization` as og on o.city = og.city')->where($map)->field('o.cellphone, o.number, o.category, og.address')->find();

		if (!$item) {
			return false;
		}

        if ($item['category'] == 2) {
            return $this->note->send($item['cellphone'], array('orderNumber' => $item['number'], 'msg' => $item['address']), 'SMS_15475218');
        } elseif ($item['category'] == 1) {
            return $this->note->send($item['cellphone'], array('orderNumber' => $item['number']), 'SMS_15555150');
        } elseif ($item['category'] == 3) {
            return $this->note->send($item['cellphone'], array('orderNumber' => $item['number'], 'msg' => $item['address']), 'SMS_44445559');
        }
	}
}