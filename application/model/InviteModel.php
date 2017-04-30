<?php
namespace app\model;

/**
 * 邀约
 */
class InviteModel extends ModelModel
{
	private $RouteModel = null;
	private $CustomerModel = null;
	private $StartTimeModel = null;
	private $BedModel = null;
	private static $GenerateOrderTimestamp = null;  //生成订单时的时间戳
	private static $GenerateOrderIndex = 1;  //同一秒内生成订单的序列号

	/**
	 * 输出是否公开的状态状态
	 * @param  string $value 0 输出‘否’ 1 输出‘是’
	 * @author huangshuaibin 
	 * @return bool        true or false
	 */
	public function getIsPublicAttr($value)
	{
		$status = array(
						'0' => '否',
						'1' => '是'
						);
		$IsShow = $status[$value];

		//如果没有$IsShow变量存在，默认输出  ‘是’,否则根据状态输出对应的状态
		if (isset($IsPay)) {
			return $IsShow;
		} else {
			return $status[0];
		}
	}

	public function getStatusAttr($value)
	{
		$status = array(
						'0' => '进行',
						'1' => '完结',
						);
		$InviteStatus = $status[$value];

		if (isset($InviteStatus)) {
			return $InviteStatus;
		} else {
			return $status[0];
		}
	}
	/**
	 * 通过该Model的外键获取Route对应的对象
	 * @return Objct 获取的RouteModel对象
	 */
	public function getRouteModel()
	{
		if (null === $this->RouteModel) {
			$RouteId = $this->getData('route_id');
			$this->RouteModel = RouteModel::get($RouteId);
		}

		return $this->RouteModel;
	}

	/**
	 * 获取用户model通过该对象的外键customer_id
	 * @return object 前台客户对象
	 */
	public function getCustomerModel()
	{
		if (null === $this->CustomerModel) {
			$CustomerId = $this->getData('customer_id');
			$this->CustomerModel = CustomerModel::get($CustomerId);
		}

		return $this->CustomerModel;
	}

	/**
	 * 通过该对象中的start_time_id获取StartTimeModel
	 * @return object StartTimeModel
	 */
	public function getStartTimeModel()
	{
		if (null == $this->StartTimeModel) {
			$StartTimeId = $this->getData('id');
			$this->StartTimeModel = StartTimeModel::get($StartTimeId);
		}

		return $this->StartTimeModel;
	}
	
	/**
	 * 通过查route_id对应的询条件$map获取邀约
	 * @param  array $map 查询条件数组
	 * @return array      邀约数组
	 */
	public static function getInviteByRouteId($map)
	{
		$InviteModel = new InviteModel;
		$invitations = $InviteModel->where('route_id','in',$map)->select();
		return $invitations;
	}

	/**
	 * 改变邀约是否公开的状态	
	 * @param  int $id   邀约的id
	 * @param  int $flag 0 or 1
	 * @author huangshuaibin
	 * @return boolean       true or false
	 */
	public static function SetInviteIsPublic($id, $flag)
	{
		$invite = InviteModel::get($id);
		//flag = 1将ispublic改成
		if ($flag == 1) {
			$invite->ispublic = 1;
		}

		if ($flag == 0) {
			$invite->ispublic = 0;
		}

		if (false == $invite->save()) {
			return false;
		}

		return true;
	}
	/**
	 * 通过订单状态以及用户的ID获取获取自己订单的列表
	 * $status=1的时候表名要取出的邀约订单已经成型
	 * $status=0                            未成型
	 * @param  int $status 0 or 1
	 * @param  int $CustomerId 用户id
	 * @author huangshuaibin
	 * @return array         满足条件的邀约订单
	 */
	public static function getInviteByCustomerIdAndStatus($status, $CustomerId)
	{
		$InviteModel = new InviteModel;
		$invites = $InviteModel->where('customer_id', '=', $CustomerId)->select();

		//建立临时数组,作为取出邀约的查询条件
		$temp = [];

		//取出状态是公开的邀约
		if (1 == $status) {
			foreach ($invites as $key => $value) {
				if (1 == $value->status) {
					array_push($temp, $value->id);
				}
			}
		}

		//取出状态是不公开的订单
		if (0 == $status) {
			foreach ($invites as $key => $value) {
				if (1 == $value->status) {
					array_push($temp, $value->id);
				}
			}
		}

		$invitations = $InviteModel->where('id', 'in', $temp)->select();
		return $invitations;
	}

	/**
	 * 保存邀约，保存对应的床位信息
	 * @param  string $stringInvitation 前台传来的邀约的字符串，以及六个用户的床位信息
	 * @author huangshuaibin
	 * @return array example ['openid'=>'oUpF8uMuAJO_M2pxb1Q9zNjWeS6o', 'number'=>'20150806125346', money: 676元]
	 */
	public static function saveInvitation($stringInvitation)
	{
	    $result = [];
		$Invitation = json_decode($stringInvitation);
		$customerId = $Invitation->customerId;
		$InviteModel = new InviteModel;

		//邀约的相关信息放入InviteModel的对象中
		$InviteModel->customer_id = $Invitation->customerId;
		//$InviteModel->set_out_time = $Invitation->setOutTime;
		$InviteModel->back_time = $Invitation->backTime;
		$InviteModel->route_id = $Invitation->routeId;
		$InviteModel->is_public = $Invitation->isPublic;
		$InviteModel->deadline = $Invitation->deadLine;
        $result['openid'] = $Invitation->openid;
		//	保存邀约号
		$InviteModel->number = self::setInviteNumber($customerId);
		//
        $InviteModel->person_num = 6;
        $InviteModel->pay_num = 0;
        $InviteModel->unpay_num = 6;
		//保存邀约
		$InviteModel->save();

		//去除邀约的id,用于后边的保存
		$inviteId = $InviteModel->id;
		
		//遍历保存六个床位
		for ($i=0; $i < 6; $i++) { 
			$BedModel = new BedModel;

			$BedModel->invite_id = $inviteId;
			$BedModel->sex = $Invitation->roomDatas[$i]->sex;

			//TODO只是把前台的数据存入了数据库，并没有根据对年龄的范围进行判断，前后台对于年龄的标识一致后，进行进一步改写
			$BedModel->old = $Invitation->roomDatas[$i]->old;
			$BedModel->money = $Invitation->roomDatas[$i]->money;
			$BedModel->type = $Invitation->roomDatas[$i]->type;

			//如果前台的床位isPay字段是1的情况，表示该床位是当前用户的床位
			if (1 === $Invitation->roomDatas[$i]->isPay) {
				$OrderModel = new OrderModel;
				$OrderModel->customer_id = $Invitation->customerId;
				$OrderModel->invite_id = $inviteId;
				$number = self::setOrderNumber();
				$OrderModel->number = $number;
				$OrderModel->save();
				$result['number'] = $number;
                $result['money'] = $Invitation->roomDatas[$i]->money;
				$OrderId = $OrderModel->getData('id');
				$BedModel->order_id = $OrderId;
				//保存customer_id进相应的床位表
				$BedModel->customer_id = $Invitation->customerId;
			}

			$BedModel->save();
		}

		return $result;
	}

	/**
	 * 生成订单编号，订单编号格式如下:
	 * 日期+时间戳后五位+同一秒内生成的第几条订单
	 * eg:2016093032434001
	 * @return string             订单编号
	 * @author chuhang 
	 */
	static public function setOrderNumber()
	{
	    //获取当前时间
		$date = date("Ymd");
        $timestamp = substr(time(), -5, 5);

        //判断在同一秒内是否有订单生成，如果有则进行累加
        if (time() !== self::$GenerateOrderTimestamp) {
            //如果同一秒内没有其他订单生成，则更新生成订单的时间戳，并对订单序列号更新
            self::$GenerateOrderTimestamp = time();
            self::$GenerateOrderIndex = 1;
        } else {
            //同一秒内有 其他订单生成，序列号加一
            self::$GenerateOrderIndex += 1;
        }

        //生成订单编号
        $result = $date . $timestamp . sprintf("%'.03d", self::$GenerateOrderIndex);

        return $result;
	}

	/**
	 * 生成邀约编号
	 * @param $customerId
	 * @return string
	 * @author: mengyunzhi www.mengyunzhi.com
	 * @Date&Time:2017-04-20 15:43
	 */
	static public function setInviteNumber($customerId)
	{
		$date = date("Ymd");
		$timestamp = substr(time(), -5, 5);

		return "y" . $date . $timestamp . $customerId;
	}

	/*
	 * 应邀
	 * @param $customerId, $invitationId, $bedId
	 * @return false true
	 * */
    static public function toCatchTheInvite($customerId, $invitationId, $bedId)
    {
        // 获取要支付床位的金额和并给床位上的customer_id赋值
        if (empty($bedId)) {
            return false;
        }
        // 获得床位m层
        $BedModel = BedModel::get($bedId);

        // 获取要支付的金额
        $money = $BedModel->getData('money');
        if(empty($money)) {
            return false;
        }
        // 调用微信接口并支付

        // 向床位model中添加customer_id
        $BedModel->customer_id = $customerId;

       

        if (empty($invitationId)) {
            return false;
        }
        // 获取邀约的model并保存支付人数和未支付人数
        $Invite = InviteModel::get($invitationId);
        $payNum = $Invite->pay_num;
        $Invite->pay_num = $payNum + 1;
        $Invite->unpay_num = $Invite->person_num - $payNum - 1 ;

        // 保存邀约
        $Invite->save();

        // 生成一条order数据
        $OrderModel = new OrderModel;
        $OrderModel->customer_id = $customerId;
        $OrderModel->invite_id = $invitationId;
        // 生成订单号
        $OrderModel->number = self::getOrderNumber($customerId);
        $OrderModel->save();
        $OrderId = $OrderModel->getData('id');
        $BedModel->order_id = $OrderId;
         // 保存数据
        $BedModel->save();
        return true;
    }
    /*
     * 去判断是否订单所有的人都支付完成，如果是就改变所有ｏｒｄｅｒ的状态为２
     * @param int $inviteId
     * @return bool
     * */
    public function isAllPayed($inviteId) {
        $InviteModel = self::get($inviteId);
        // 首先判断是否全部支付
        $pay_num = $InviteModel->getData('pay_num');
        $person_num = $InviteModel->getData('person_num');
        if ($pay_num + 1 === $person_num) {
            // 修改邀约中的数据
            $InviteModel->pay_num = $pay_num + 1;
            $InviteModel->unpay_num = $InviteModel->getData('unpay_num') - 1;
            $InviteModel->save();

            // 修改订单中的状态全为２
            $OrderModel = new OrderModel();
            $OrderModels = $OrderModel->where('invite_id', '=', $inviteId)->select();
            foreach ($OrderModels as $key => $value) {
                $value->status = 1;
                $value->save();
            }
            return true;
        }
        // 修改邀约中的数据
        $InviteModel->pay_num = $pay_num + 1;
        $InviteModel->unpay_num = $InviteModel->getData('unpay_num') - 1;
        $InviteModel->save();
        return true;
    }
}
