<?php

/**
 * 后台首页
 * @author Aaron
 *
 */
namespace Admin\Controller;
use Common\Common\Tools;
use Common\Controller\AdminController;
class IndexController extends AdminController {
	public function index() {
		$my_admin = session();
		$model =M('system_user');
		//管理员信息
		$admin = $model->where(array('user_name'=>$my_admin['userName'],'status'=>1))->find();
		//统计管理员
		$count = $model->where(array('user_name'=>$my_admin['userName'],'status'=>1))->count();
		$admin['num']=$count;
		$this->info = $admin;
		//系统信息
		$system_info = array(
			'cyh_version' => '车越汇 Ver 1.0 Beta [<a href="http://www.cheyuehui.com/" class="blue" target="_blank">官方首页</a>]',
			'server_domain' => $_SERVER['SERVER_NAME'] . ' [ ' . gethostbyname($_SERVER['SERVER_NAME']) . ' ]',
			'server_os' => PHP_OS,
			'web_server' => $_SERVER["SERVER_SOFTWARE"],
			'php_version' => PHP_VERSION,
			'mysql_version' => mysql_get_server_info(),
			'upload_max_filesize' => ini_get('upload_max_filesize'),
			'max_execution_time' => ini_get('max_execution_time') . '秒',
			'safe_mode' => (boolean) ini_get('safe_mode') ?  'onCorrect' : 'onError',
			'zlib' => function_exists('gzclose') ?  'onCorrect' : 'onError',
			'curl' => function_exists("curl_getinfo") ? 'onCorrect' : 'onError',
			'timezone' => function_exists("date_default_timezone_get") ? date_default_timezone_get() : '否'
		);
		$sysinfo = \Admin\Common\Sysinfo::getinfo();
		$os = explode(' ', php_uname());
		//网络使用状况
		$net_state = null;
		if ($sysinfo['sysReShow'] == 'show' && false !== ($strs = @file("/proc/net/dev"))){
			for ($i = 2; $i < count($strs); $i++ ){
				preg_match_all( "/([^\s]+):[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $strs[$i], $info );
				$net_state.="{$info[1][0]} : 已接收 : <font color=\"#CC0000\"><span id=\"NetInput{$i}\">".$sysinfo['NetInput'.$i]."</span></font> GB &nbsp;&nbsp;&nbsp;&nbsp;已发送 : <font color=\"#CC0000\"><span id=\"NetOut{$i}\">".$sysinfo['NetOut'.$i]."</span></font> GB <br />";
			}
		}
		$this->os = $os;
		$this->net_state = $net_state;
		$this->sysinfo = $sysinfo;
		$this->system = $system_info;

		$this->display ();
	}

	//更新油价数据
	public function checkApi(){
		if(I('request.type')=='up'){
			$requestData = @file_get_contents(C('oil_city_url').'?'.C('oil_city_par'));
			$result = json_decode($requestData,true);
			if($result['resultcode'] == 200){
				$result['time'] = date('Y-m-d H:i:s',time());

				foreach($result['result'] as &$v)
				{
					$b90 = explode('：',$v['b90']);
					(count($b90)==2)?$v['b90'] = $b90[1]:'';
					$b93 = explode('：',$v['b93']);
					(count($b93)==2)?$v['b93'] = $b93[1]:'';
					$b97 = explode('：',$v['b97']);
					(count($b97)==2)?$v['b97'] = $b97[1]:'';
				}

				@file_put_contents('oil_city.json',json_encode($result));
                $this->addLog('time='.$result['time'],1);// 记录操作日志
				$this->ajaxReturn('已成功更新！'.$result['time']);
			}else{
				@file_put_contents('./logs/err.log',date('Y-m-d H:i:s',time()).'接口请求错误[错误码：'.$result['error_code'].']'.PHP_EOL,FILE_APPEND);
                $this->addLog('time='.$result['time'].'&error_code='.$result['error_code'],0);// 记录操作日志
				$this->ajaxReturn('接口请求错误[错误码：'.$result['error_code'].']');
			}
		}else{
			$result = @file_get_contents('oil_city.json');
			$result = json_decode($result,true);
			$this->ajaxReturn($result['time']);
		}
	}

	//检查油价数据有效性
	public function checkOilData(){
		$requestData = @file_get_contents('oil_city.json');
		$result = json_decode($requestData,true);
		foreach($result['result'] as &$v)
		{
			$b90 = explode('：',$v['b90']);
			(count($b90)==2)?$v['b90'] = $b90[1]:'';
			$b93 = explode('：',$v['b93']);
			(count($b93)==2)?$v['b93'] = $b93[1]:'';
			$b97 = explode('：',$v['b97']);
			(count($b97)==2)?$v['b97'] = $b97[1]:'';
		}
		@file_put_contents('oil_city.json',json_encode($result));
		$this->ajaxReturn($result);
	}

	//今日新增车行
	public function storeInfo(){
		$mSt = M("store");
		$whereToday['add_time']=array(array('egt',date('Y-m-d',time())),array('lt',date('Y-m-d',strtotime("+1 day"))));
		$store_info =array(
			'todayAdd' =>$mSt->field("store_user,store_name,store_address")->where($whereToday)->select()
		);
		$this->ajaxReturn($store_info);
	}

	//今日新增车主
	public function ownerInfo(){
		$mUO = M("user_owner");
		$whereToday['add_time']=array(array('egt',date('Y-m-d',time())),array('lt',date('Y-m-d',strtotime("+1 day"))));
		$owner_info =array(
			'todayAdd' =>$mUO->field("phone,nickname,head_img")->where($whereToday)->select()
		);
		$this->ajaxReturn($owner_info);
	}

	//检查油价数据有效性
	public function billInfo(){
		$wYear = I("post.year",date('Y',time()));
		$wType = I("post.type",'chk');
		switch($wType){
			case 'yy':  //所有记录
				$wType = 'ticket_time';
				break;
			case 'use':  //已预约使用的
				$wType = 'ticket_use_time';
				break;
			case 'sub':  //已预约，车行完成服务的
				$wType = 'ticket_sub_time';
				break;
			default:  //平台确认的账单
				$wType = 'ticket_check_time';
				break;
		}
		$Model = new \Think\Model();
		$info = $Model->query("select DATE_FORMAT($wType,'%Y%m') dt,count(ticket_id) cNum from cyh_car_ticket where DATE_FORMAT($wType,'%Y') = $wYear group by DATE_FORMAT($wType,'%Y%m') ORDER BY dt asc");
		if(!$info) $info=0;
		$this->ajaxReturn($info);
	}

	public function check_char()
	{
		$v=Tools::CheckChar();
		$this->ajaxReturn($v);
	}

}