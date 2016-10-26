<?php
/**
 * 后台公共类，做基础服务
 * 用户检查，权限检查，获取菜单
 */
namespace Common\Controller;
class AdminController extends BaseController
{
    /**
     * 初始化后台数据
     */
    public function _initialize()
    {
        BaseController::_initialize();

//        // 删除缓存
//        $cache = S('Category');
//        unset($cache);
//        S('Category',null);
//        //刷新栏目索引缓存开始
//        $CA=M('category');
//        $data = $CA->order("listorder ASC")->select();
//        $CategoryIds = array();
//        foreach ($data as $r) {
//            $CategoryIds[$r['cat_id']] = array(
//                'cat_id' => $r['cat_id'],
//                'parentid' => $r['parentid'],
//            );
//        }
//        cache("Category", $CategoryIds,'2');//暂时定为2秒
//        //刷新栏目索引缓存结束

        $module = strtolower(MODULE_NAME);
        $controller = strtolower(CONTROLLER_NAME);
        $action = strtolower(ACTION_NAME);
        //过滤不需要登录的操作
        $NOUser = array('login', 'logout');
        // 过滤不需要检验权限的操作
        if (!in_array($action, $NOUser)) {
            $this->CheckUser();
            //首页不验证权限
            if ($module == 'admin' and $controller == 'index' and $action == 'index') {
            } else {
                //权限验证
                if(!authCheck(MODULE_NAME."/".CONTROLLER_NAME."/".ACTION_NAME,session('uid'))){
                    $this->addLog(MODULE_NAME."/".CONTROLLER_NAME."/".ACTION_NAME,2);// 记录操作日志
                    session('link', null);
                    $this->error('你没有权限!'.session('uid'));
                }
            }
        }
        //获取左边菜单
//        $this->getMenu();

    }

    //写入日志
    public function addLog( $doing,$status = 1 ) {
        $logData=M('system_action_log');
        $data = array();
        $data['ip']    = get_client_ip();
        $data['time']  = date("Y-m-d H:i:s");
        $data['user_name'] = session('userName');
        $data['user_id'] = session('uid');
        $data['module'] = MODULE_NAME;
        $data['action'] = ACTION_NAME;
        $data['querystring'] = $doing;
        $data['status'] = $status;
        $logData->data($data)->add();
    }

    //写入登录日志 by chen
    public function insert_log($userid,$username,$password,$status,$loginip,$login_location=''){
        $LoginLogData=M('system_login_log');
        $LogData = array();
        $LogData['user_id']=$userid;
        $LogData['user_name']=$username;
        $LogData['login_time']=date("Y-m-d H:i:s");
        $LogData['login_ip']=$loginip;
        $LogData['login_ip']=$login_location;
        $LogData['status']=$status;
        $LogData['log_password']=$password;
        $LoginLogData->data($LogData)->add();
    }

    /**
     * 检查用户相关信息
     * 是否登录,记录一些信息
     */
    public function CheckUser()
    {
        $this->uid = session('uid'); //用户id
        //检查是否登录
        if (isset ($this->uid)) {
            $this->userName = session('userName'); //用户名称
            $this->remark = session('remark'); //用户权限
            return true;
        } else {
            session('link', MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME);
            $this->redirect('Admin/User/Login');
        }
    }

    /**
     * 获取管理后台左边菜单
     * 不同的角色获取不同的菜单
     */
    public function getMenu()
    {
        $P = M('privileges');
        if ($this->privileges != 'ALL') {
            $privileges = explode(',', $this->privileges);
            foreach ($privileges as $k => $v) {
                if ($k == 0) {
                    $where = 'privilege_id = ' . $privileges [$k];
                } else {
                    $where .= ' or privilege_id = ' . $privileges [$k];
                }
            }
        }
        //获取角色所有的权限对应的菜单
        $menu = $P->field(true)->where($where)->order('sort')->select();
        $module_name = strtolower(MODULE_NAME);
        $controller_name = strtolower(CONTROLLER_NAME);
        $action_name = strtolower(ACTION_NAME);
        foreach ($menu as $key => $value) {
            //把数据库字段转小写
            $module_nameS = strtolower($value['module_name']);
            $controller_nameS = strtolower($value['controller_name']);
            $action_nameS = strtolower($value['action_name']);
            $menu[$key]['url'] = U($value['module_name'] . '/' . $value['controller_name'] . '/' . $value['action_name']);
            if ($module_name == $module_nameS and $controller_name == $controller_nameS and $action_name == $action_nameS) {
                // 给当前和上级菜单增加选中
                $menu [$key]['active'] = 'active';
                foreach ($menu as $k => $v) {
                    if ($menu[$k]['privilege_id'] == $menu[$key]['pid']) {
                        $menu[$k]['active'] = 'active';
                    }
                }
            }
        }
        $this->menu = $this->TreeArray($menu, 'privilege_id', 'pid', '0', 'sub', '1');
        // die(var_dump($this->menu));
    }

    /**
     * 公共的递归，支持多级遍历
     * @param array $Array 需要递归的数组
     * @param int $ID 数组id键名
     * @param string $PinNmae 上级id键名
     * @param int $Pid 开始的id
     * @param string $SubNmae 递归后下级的键名
     * @param string $menu 为1只显示菜单
     */
    public function TreeArray($Array, $Id = 'id', $PinNmae = 'pid', $Pid = '0', $SubNmae = 'sub', $menu = '0')
    {
        foreach ($Array as $k => $v) {
            if ($Pid) {
                if ($Array [$k] [$PinNmae] == $Pid) {
                    $Arr = $Array [$k];
                    $Arr [$SubNmae] = $this->TreeArray($Array, $Id, $PinNmae, $Array [$k] [$Id], $SubNmae, $menu);
                    //兼容原来的菜单列表
                    if ($menu) {
                        if ($Array [$k] ['is_show'] == 1) {
                            $arr[] = $Arr;
                        }
                    } else {
                        $arr[] = $Arr;
                    }
                }
            } else {
                if ($Array [$k] [$PinNmae] == $Pid) {
                    $Arr = $Array [$k];
                    $Arr [$SubNmae] = $this->TreeArray($Array, $Id, $PinNmae, $Array [$k] [$Id], $SubNmae, $menu);
                    //兼容原来的菜单列表
                    if ($menu) {
                        if ($Array [$k] ['is_show'] == 1) {
                            $arr[] = $Arr;
                        }
                    } else {
                        $arr[] = $Arr;
                    }
                }
            }
        }
        return $arr;
    }

    /**
     * ajax提示专用
     * @param $status 执行状态，默认为200，成功
     * @param $info 提示信息，默认为 ‘操作成功’
     * @param $url 跳转的url，默认为空
     */
    public function Reminder($status = 200, $info = '操作成功', $url = '')
    {
        $data = array(
            'code' => $status,
            'info' => $info,
            'url' => $url
        );

        $this->ajaxReturn($data);
    }
}