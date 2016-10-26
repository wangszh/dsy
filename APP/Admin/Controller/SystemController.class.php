<?php
/**
 * 后台系统管理
 * 系统配置，系统功能管理
 * 管理员管理
 * @author Aaron
 */
namespace Admin\Controller;
use Common\Controller\AdminController;
class SystemController extends AdminController
{
	/*系统管理列表
    */
	public function userList($n = '15')
	{
		$admin = M('system_user');
		$count = $admin->count();
		$Page = new \Think\Page($count, $n);
		$this->Page = $Page->show();
		$list = $admin->alias('us')->join('LEFT JOIN dsy_auth_group_access as aga on aga.uid = us.user_id')
            ->join('LEFT JOIN dsy_auth_group as ag on aga.group_id = ag.id')
            ->field('us.user_id,us.user_name,us.status,us.remark,us.last_login_time,us.last_login_ip,us.last_location,ag.title')
            ->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->list=$list;

		$this->display();
	}

    /*显示帐号登录历史信息
    */
    public function LoginLog($user_id)
    {
        if (I('get.user_id','')) {
            $where['userid'] = $user_id;
            $where['status'] = 1;
            //查询数据
            $model = M('user_log');
            $list = $model->field('userid,username,logintime,loginip')->where($where)->order('logintime desc')->select();
            $this->ajaxReturn($list);
        }
    }

	/*后台帐号修改
    */
	public function userEdit($user_id = '0')
	{
        if (IS_POST) {
            $admin = M('system_user');
			$us['user_name']=I('post.username');
			$us['remark']=I('post.remark');
			$aga['group_id']=I('post.groupId');

            if (!$admin->create($_POST,2)){
                $this->error($admin->getError());
            }
            if (false!==$admin->where(array('user_id'=>$user_id))->save($us)) {
                //更新角色
                M('auth_group_access')->where(array('uid'=>$user_id))->save($aga);
                $this->addLog('group_id='.$aga['group_id'].'&remark='.$us['remark'].'&user_name='.$us['user_name'].'&user_id='.$user_id,1);// 记录操作日志
                $this->success('编辑管理帐号成功', U('Admin/System/userList'));
            } else {
                $this->addLog('group_id='.$aga['group_id'].'&remark='.$us['remark'].'&user_name='.$us['user_name'].'&user_id='.$user_id,0);// 记录操作日志
                $this->error('编辑管理帐号出错');
            }
		} else {
			$admin = M('system_user');
			//获取帐号信息
			if ($user_id) {
				$info = $admin->alias('us')->join('LEFT JOIN dsy_auth_group_access as aga on us.user_id = aga.uid')
                    ->field('us.user_id,us.user_name,us.status,us.remark,us.last_login_time,us.last_login_ip,us.last_location,aga.uid,aga.group_id,us.province_id,us.city_id')
                    ->find($user_id);
				$this->info = $info;
			}
            $m=M('auth_group');
            $data=$m->where('status=1')->field('id,title')->select();
            $this->data=$data;

			$this->display();
		}
	}

    /*修改后台帐号密码
    */
    public function chanPass($user_id) {
        if (IS_POST) {
            $oldPass = I('post.oldpassword', '', 'trim');
            if (empty($oldPass)) {
                $this->error("请输入旧密码！");
            }
            $newPass = I('post.password', '', 'trim');
            $new_PwdConfirm = I('post.password2', '', 'trim');
            if ($newPass != $new_PwdConfirm) {
                $this->error("两次密码不相同！");
            }
            $admin = D('system_user');
            $map = array();
            $map['user_id'] = $user_id;
            $userInfo = $admin->where($map)->find();
            if (empty($userInfo)) {
                $this->error = '该用户不存在！';
            }
            if (!$admin->create($_POST,2)){
                $this->error($admin->getError());
            }
            if (false!==$admin->where('user_id = ' . $user_id)->save()) {
                $this->addLog('user_id='.$user_id,1);// 记录操作日志
                $this->success("密码更新成功！", U("Admin/System/userList"));
            } else {
                $this->addLog('user_id='.$user_id,0);// 记录操作日志
                $this->error("密码更新失败！", U('Admin/System/userList'));
            }
        } else {
            $admin = M('system_user');
            //获取帐号信息
            if ($user_id) {
                $info = $admin->field('user_id,user_name')->find($user_id);
                $this->info = $info;
            }
            $this->display();
        }
    }

    /*验证密码是否正确
     */
    public function verifyPass($user_id) {
        if (IS_POST) {
            $Password= I('post.param');
            $UserID= $user_id;
            $map=array();
            $map['password']=md5($Password);
            $map['user_id']=$UserID;
            $UId = M('system_user')->field('password')->where($map)->find();
            if (md5($Password)!==$UId['password']) {
                $jsonData='{
                "info":"原密码不正确，请重新输入！",
                "status":"n"
                }';
                echo $jsonData;
            } else {
                $jsonData='{
                "info":"原密码输入正确,验证通过！",
                "status":"y"
                }';
                echo $jsonData;
            }
        }
        else{
            $jsonData='{
                "info":"对不起，非法操作！",
                "status":"n"
                }';
            echo $jsonData;
        }
    }

	/*管理帐号删除
   */
	public function userDel($user_id = '0')
	{
		$arr = array_diff(explode(',',$user_id),C('ADMINISTRATOR'));
		if(!$arr){
			$this->error("不允许对超级管理员执行该操作!");
		}else{
			$DU = M('system_user');
			$user_id = implode(',',$arr);
			if ($DU->delete($user_id)) {
				$whereAGA['uid'] = array('in',$arr);
				M('auth_group_access')->where($whereAGA)->delete();
				$this->addLog('user_id='.$user_id,1);// 记录操作日志
				$this->success('删除管理帐号成功', U('Admin/System/userList'));
			} else {
				$this->addLog('user_id='.$user_id,0);// 记录操作日志
				$this->success('删除管理帐号错误', U('Admin/System/userList'));
			}
		}
	}

	/*管理帐号新增
    */
	public function userAdd()
	{
		if (IS_POST) {
            //所属角色id
            $groupId=I("post.groupId");
			$admin = D('system_user');
			if (!$admin->create(I('post.'),1)){
				// 如果创建失败 表示验证没有通过 输出错误提示信息
				$this->error($admin->getError());
			}
			if($admin->where(array('user_name'=>I('post.user_name')))->find())$this->error('帐号已存在');
            $uid=$admin->add();
			if ($uid) {
                //用户$uid
                $where['uid']=$uid;
                $where['group_id']=$groupId;
                $g=M("auth_group_access");//角色
                if($g->add($where)) {
                    $this->addLog('user_name='.I('user_name').'&group_id='.$groupId.'&id='.$uid,1);// 记录操作日志
                    $this->success('添加管理帐号成功', U('Admin/System/userList'));
                }
                else
                {
                    $this->addLog('user_name='.I('user_name').'&group_id='.$groupId.'&id='.$uid,0);// 记录操作日志
                    $this->error('添加管理帐号时，权限分配出错');
                }
			} else {
                $this->addLog('user_name='.I('user_name').'&group_id='.$groupId.'&id='.$uid,0);//记录操作日志
				$this->error('添加管理帐号出错');
			}

		} else {
            $m=M('auth_group');
            $data=$m->where('status=1')->field('id,title')->select();
            $this->data=$data;

			$this->display();
		}
	}


	/**
	 * 帐号状态修改
	 */
	public function changeStatus($method=null){
		$user_id = I('get.id',0);
		$arr = array_diff(explode(',',$user_id),C('ADMINISTRATOR'));
		if(!$arr){
			$this->error("不允许对超级管理员执行该操作!");
		}
		$where['user_id'] = $user_id;
		switch ( strtolower($method) ){
			case 'forbiduser'://禁用
				$data['status']    =  0;
				$msg = '已禁用';
				break;
			case 'resumeuser'://启用
				$data['status']   =  1;
				$msg = '已启用';
				break;
			default:
				$this->error('参数非法');
		}

		if(M('system_user')->where($where)->save($data)!==false ) {
			$this->success($msg);
		}else{
			$this->error('操作失败');
		}
	}

	//权限菜单列表
	public function privilegesList($type='5')
	{
		$P = M('privileges');
		$this->type = $type;
		if($type == 3 or $type == 4){
			if($type == 3){
				$privilege_id = explode(',',C('MAINSHOPPRIVILEGES'));
			}elseif ($type == 4) {
				$privilege_id = explode(',',C('SUBSHOPPRIVILEGES'));
			}
			foreach ($privilege_id as $key => $value) {
				if($key){
					$where .=' or privilege_id = '.$value;
				}else{
					$where =' privilege_id = '.$value;
				}
			}
		}
		$list = $P->where($where)->order('sort')->select();
		$this->list = $this->TreeArray($list,'privilege_id');
		$this->display();
	}

	//添加编辑权限菜单
	public function privilegesInfo($privilege_id='0')
	{
		$P = M('privileges');
		if(IS_POST){
			if(!$_POST['is_show']){
				$_POST['is_show'] = '0';
			}
			if($privilege_id){
				unset($_POST['pid']);
				if($P->create() and $P->where('privilege_id = '.$privilege_id)->save()){
					$this->success('修改权限信息成功', U('System/privilegesList'));
				}else{
					$this->error('修改权限信息失败');
				}
			}else{
				if($P->create() and $P->add()){
					$this->success('添加权限信息成功', U('System/privilegesList'));
				}else{
					$this->error('添加权限信息失败');
				}
			}
		}else{
			$this->info = $P->field(true)->find($privilege_id);
			//获取顶级菜单
			$this->PList = $P->where('pid=0')->field(true)->select();
			$this->display();
		}
	}

	//删除权限菜单
	public function delPrivileges($privilege_id)
	{
		$P = M('privileges');
		$count = $P->where('pid = '.$privilege_id)->count();
		if($count){
			$this->error('当前菜单下面还有'.$count.'个权限。不可以删除！');
		}else{
			//这里暂时不放实际删除功能
			$this->success('删除权限菜单成功', U('System/privilegesList'));
		}
	}

	//系统设置，默认公共类型
	public function system($type='1')
	{
		$C = M('config');
		if(IS_POST){
            $this->addLog('type='.$type,0);// 记录操作日志
			$this->error('暂时只允许修改版本号！');
		}else{
			$this->type = $type;
			$this->list = $C->where('config_type = '.$this->type)->select();
			$this->display();
		}
	}
	//系统设置，默认公共类型
	public function upVersion()
	{
		$ver = I('ver');
		if(!I('num')) $this->error('参数错误');
		switch($ver)
		{
			case 1:
				$where['config_field']='APP_IOS_VERSION_FINAL';
				$data['config_value'] = I('num');
				M('config')->where($where)->data($data)->save();
				break;
			case 2:
				$where['config_field']='APP_IOS_VERSION_TEST';
				$data['config_value'] = I('num');
				M('config')->where($where)->data($data)->save();
				break;
			case 3:
				$where['config_field']='APP_ANDROID_VERSION';
				$data['config_value'] = I('num');
				M('config')->where($where)->data($data)->save();
				break;
			default;
				$this->error('参数错误');
				break;
		}
        $this->addLog('config_value='.I('num').'&ver='.$ver,1);// 记录操作日志
		$this->success('版本号更新完毕');
	}

	//添加编辑配置项
	public function systemInfo($config_id)
	{
		# code...
	}

	/*
	 * 信息反馈管理--列表
	 * by chen
	*/
	public function feedback($n = '15')
	{
		//过滤筛选条件
		$where = array();
//		switch($_GET['searchType'])
//		{
//			case'message_id':
//				$_GET['searchkey']&&$where['message_id'] = $_GET['searchkey'];
//				break;
//			case'owners_id':
//				$_GET['searchkey']&&$where['owners_id'] = $_GET['searchkey'];
//				break;
//			default:
//				break;
//		}
		$cid = I('get.cid');
		if($cid <> '') {
			$where['fb.is_check'] = $cid;
		}

		//查询数据
		$model = M('feedback');
		$count = $model->alias('fb')->join('LEFT JOIN dsy_user_owner as uo on fb.owner_id = uo.owner_id')
			->join('LEFT JOIN dsy_store as s on s.store_id = fb.store_id')
			->where($where)->count();
		$Page = new \Think\Page($count, $n);
		$this->Page = $Page->show();
		$this->list = $model->alias('fb')->join('LEFT JOIN dsy_user_owner as uo on fb.owner_id = uo.owner_id')
			->join('LEFT JOIN dsy_store as s on s.store_id = fb.store_id')
			->field('fb.message_id,fb.contents,fb.addtime,fb.is_check,uo.nickname,uo.phone,s.store_name')->where($where)->order('message_id desc')
			->limit($Page->firstRow . ',' . $Page->listRows)->select();

     	$this->display();
	}

	/*
	 * 信息反馈管理--删除
	 * by chen
	*/
	public function feedbackDel($message_id)
	{
		$DM = M('feedback');
		if ($DM->delete($message_id)) {
            $this->addLog('message_id='.$message_id,1);// 记录操作日志
			$this->success('删除一条反馈信息成功', U('Admin/System/feedback'));
		} else {
            $this->addLog('message_id='.$message_id,0);// 记录操作日志
			$this->error('删除反馈信息错误', U('Admin/System/feedback'));
		}
	}

	/*
	 * 信息反馈管理--处理
	 * by chen
	*/
	public function feedbackCheck($message_id)
	{
		$DM = M('feedback');
		$data['is_check'] =1;
		if ($DM->data($data)->where('message_id = '.$message_id)->save()) {
            $this->addLog('is_check='.$data['is_check'].'&message_id='.$message_id,1);// 记录操作日志
			$this->success('OK!');
		} else {
            $this->addLog('is_check='.$data['is_check'].'&message_id='.$message_id,0);// 记录操作日志
			$this->error('错误!');
		}
	}

	/*
    * 全国城市校验
     */
	public function CheckCity()
	{
		$pub_province = M('pub_province')->field(true)->select();
		$pub_city = M('pub_city')->field(true)->select();
		$pub_area = M('pub_area')->field(true)->select();
		$this->pub_province=$pub_province;
		$this->display();
	}

	/*
	 * 清空数据！发布前准备。
	 * */
	public  function ClearData()
	{
		if($_GET['t'] == 'logs'){
			@file_put_contents('./logs/err.log','');
			@file_put_contents('./logs/alipay.txt','');
			@file_put_contents('./logs/JPushErr.txt','');
            $this->addLog('logs='.$_GET['t'],1);// 记录操作日志
			$this->success('数据已清空！',U('system/ClearData'));
		}elseif($_GET['t'] == 'file'){
			$dirName[0]='APP/Runtime/Cache/';
			foreach($dirName as $dn) {
				$base_dir = $dn;
				$fso = opendir($base_dir);
				while ($flist = readdir($fso)) {
					$fso1 = opendir($base_dir . '/' . $flist);
					while ($flist1 = readdir($fso1)) {
						unlink($base_dir . '/' . $flist. '/'.$flist1);
					}
					closedir($fso1);
					rmdir($base_dir . '/' . $flist);
				}
				closedir($fso);
			}
			$this->success('操作完毕！');
		}else $this->display();
	}
    

    /*
     *信息推送
     * 推送窗口
    */
    public function InfoPush(){
        $this->display();
    }

    /*
     *信息推送
     * 单车主推送
    */
    public function InfoPushOne(){
		if(IS_POST) {
			$us['phone'] = I('post.user_name');
			if(!M('user_owner')->where($us)->find()) $this->error('账户不存在');

			$result = $this->sendApp(I('post.content',''),'车越汇',I('post.title',''),$us['phone']);
			if($result !== false){
                $this->addLog('text='.I('text').'&title='.I('title').'&user_name='.I('post.user_name'),1);// 记录操作日志
				$this->success('发送成功！ Push Success. Response JSON:'.$result->json);
			}
			else{
                $this->addLog('text='.I('text').'&title='.I('title').'&user_name='.I('post.user_name'),0);// 记录操作日志
                $this->error('发送失败！请联系技术人员。');
            }
		}
    }

    /*
     *信息推送
     * 全体信息推送
    */
    public function InfoPushAll(){
		if(IS_POST) {
			$result = $this->sendApp(I('post.content',''),'车越汇',I('post.title',''));
			if($result !== false){
				$data['owner_id']=0;
				$data['type']='adv';
				$data['title']=I('post.title','');
				$data['content']=I('post.content','');
				$data['jump_url']='home';
				$data['is_read']=0;
				$data['add_time']=date('Y-m-d H:i:s',time());
				M('msg')->data($data)->add();
                $this->addLog('text='.I('text').'&title='.I('title').'&user_name=all',1);// 记录操作日志
				$this->success('发送成功！ Push Success. Response JSON:'.$result->json);
			}
			else {
                $this->addLog('text='.I('text').'&title='.I('title').'&user_name=all',0);// 记录操作日志
                $this->error('发送失败！请联系技术人员。');
            }
		}
    }

    /*
	 * 消息信息管理--列表
	 * by chen
	*/
    public function RedMsgList($n = '15')
    {
        //过滤筛选条件
        $where = array();
        $sType= I('get.searchType');
        $sInfo= I('get.searchInfo');
        $sKey= I('get.searchkey');
        switch($sType)
        {
            case'0':
                $where['type'] ='adv';
                break;
            case'1':
                $where['type'] ='sys';
                break;
            case'2':
                $where['type'] ='per';
                break;
            default:
                break;
        }
        switch($sInfo)
        {
            case'msg_id':
                $sKey && $where['msg_id'] = array('like','%'.$sKey."%");
                break;
            case'title':
                $sKey && $where['title'] = array('like','%'.$sKey."%");
                break;
            default:
                break;
        }

        //查询数据
        $model = M('msg');
        $count = $model->alias('m')->join('LEFT JOIN dsy_user_owner as uo on m.owner_id = uo.owner_id')
            ->where($where)->count();
        $Page = new \Think\Page($count, $n);
        $this->Page = $Page->show();
        $this->list = $model->alias('m')->join('LEFT JOIN dsy_user_owner as uo on m.owner_id = uo.owner_id')
            ->field('m.msg_id,m.owner_id,m.type,m.title,m.content,m.add_time,m.jump_url,uo.nickname,uo.phone')->where($where)->order('msg_id desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->display();
    }

    /*
	 * 消息信息管理--删除
	 * by chen
	*/
    public function RedMsgListDel($msg_id)
    {
        $DM = M('msg');
        if ($DM->delete($msg_id)) {
            $this->addLog('msg_id='.$msg_id,1);// 记录操作日志
            $this->success('删除一条消息信息成功', U('Admin/System/RedMsgList'));
        } else {
            $this->addLog('msg_id='.$msg_id,0);// 记录操作日志
            $this->error('删除消息信息错误', U('Admin/System/RedMsgList'));
        }
    }

    /*银行列表
     * by chen
    */
    public function bankList($n=20){
        //过滤筛选条件
        $where = array();
        $sType= I('get.searchType');
        switch($sType)
        {
            case'bankName':
                $where['bankName'] = array('like','%'. I('get.searchkey')."%");
                break;
            default:
                break;
        }
        $model=M('bank');
        $count=$model->where($where)->count();
        $Page = new \Think\Page($count, $n);
        $this->Page = $Page->show();
        $data=$model->limit($Page->firstRow.','.$Page->listRows)->where($where)->select();
        $this->list=$data;
        $this->display();
    }

    /*添加银行
     * by chen
    */
    public function bankAdd(){
        if (IS_POST) {
            $m=M("bank");
            if (!$m->create($_POST,1)){
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($m->getError());
            }
            $lastId=$m->add();
            if($lastId){
                $this->addLog('bankName='.I('bankName').'&bankId='.$lastId,1);// 记录操作日志
                $this->success('添加银行成功', U('Admin/System/bankList'));
            }else{
                $this->addLog('bankName='.I('bankName').'&bankId='.$lastId,0);// 记录操作日志
                $this->error('添加银行失败');
            }
        }
        else
        {
            $this->display();
        }
    }
    /*编辑银行
     * by chen
    */
    public function bankEdit($bankId='0'){
        if (IS_POST) {
            $model = M('bank');
            if (!$model->create($_POST,2)){
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($model->getError());
            }
            if (false !== $model->where('bankId = ' . $bankId)->save()) {
                $this->addLog('bankName='.I('bankName').'&bankId='.$bankId,1);// 记录操作日志
                $this->success('编辑银行成功', U('Admin/System/bankList'));
            } else {
                $this->addLog('bankName='.I('bankName').'&bankId='.$bankId,0);// 记录操作日志
                $this->error('编辑银行出错');
            }

        } else {
            $m=M('bank');
            $info = $m->find($bankId);
            $this->info=$info;
            $this->display();
        }
    }
    /*删除银行
    *by chen
    */
    public function bankDel($bankId){
        $SS = M('bank');
        if ($SS->delete($bankId)) {
            $this->addLog('bankId='.$bankId,1);// 记录操作日志
            $this->success('银行删除成功', U('Admin/System/bankList'));
        } else {
            $this->addLog('bankId='.$bankId,0);// 记录操作日志
            $this->error('银行删除失败');
        }
    }

}