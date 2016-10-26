<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-6-26
 * Time: 上午3:47
 * 权限管理，角色管理
 */

namespace Admin\Controller;
use Common\Controller\AdminController;

class AuthController extends AdminController {

    /*角色管理页面
     * by chen
    */
    public function groupList($n = '15'){
        $admin=M('auth_group');

        //过滤筛选条件
        $where = array();
        $sType= I('get.searchType');
        switch($sType)
        {
            case'id':
                $where['user_id'] = array('like','%'. I('get.searchkey')."%");
                break;
            case'name':
                $where['user_name'] = array('like','%'.I('get.searchkey')."%");
                break;
            default:
                break;
        }
        //查询数据
        $count = $admin->where($where)->count();
        $Page = new \Think\Page($count, $n);
        $this->Page = $Page->show();
        $this->list = $admin->limit($Page->firstRow . ',' . $Page->listRows)->where($where)->select();
        $this->display();
    }
    /*添加角色
     * by chen
    */
    public function groupAdd(){
        if (IS_POST) {
            $data['title']=I('groupName');
            $data['describe']=I('describe');
            $data['status']=I('status');
            $m=M("auth_group");
            if (!$m->create($_POST,1)){
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($m->getError());
            }
            $lastId=$m->add($data);
            if ($lastId) {
                $this->addLog('title='.$_POST['title'].'&id='.$lastId,1);// 记录操作日志
                $this->success('添加角色成功', U('Admin/Auth/groupList'));
            }else{
                $this->addLog('title='.$_POST['title'].'&id='.$lastId,0);// 记录操作日志
                $this->error('添加角色失败');
            }
        }
        else
        {
            $this->display();
        }
    }

    /*
     * 修改角色
    */
    public function groupEdit($id='0'){
        if (IS_POST) {
            $admin = M('auth_group');
            $data['title']=I('post.title');
            $data['describe']=I('post.describe');
            $data['status']=I('post.status');
            if (!$admin->create($_POST,2)){
                $this->error($admin->getError());
            }
            if (false!==$admin->where('id = ' . $id)->save($data)) {
                $this->addLog('title='.$_POST['title'].'&id='.$id,1);// 记录操作日志
                $this->success('修改角色成功', U('Admin/Auth/groupList'));
            } else {
                $this->addLog('title='.$_POST['title'].'&id='.$id,0);// 记录操作日志
                $this->error('修改角色失败');
            }
        }
        else
        {
            $m=M('auth_group');
            $data=$m->field('id,title,status,describe')->find($id);
            $this->info=$data;
            $this->display();
        }
    }

    /*
     * 删除角色
    */
    public function groupDel($id){
        $AGA = M('auth_group');
        $where['id']=$id;
        //判断角色组下有没有用户
        $haveUser =$AGA->alias('ag')-> join('LEFT JOIN dsy_auth_group_access as aga on ag.id = aga.group_id')->find($id);
        if ($haveUser['uid'])
        {
            $this->error('该角色组下还有用户，请先删除用户');
        }
        else
        {
            if ($AGA->where($where)->delete()) {
                $this->addLog('id='.$id,1);// 记录操作日志
                $this->success('删除角色成功');
            } else {
                $this->addLog('id='.$id,0);// 记录操作日志
                $this->error('删除角色错误');
            }
        }

    }

    /*角色授权页面
    * by chen
    */
    public function accessSelect(){
        //角色id
        $group['id']=$where['id']=I('id');
        //角色名称
        $group['name']=I('name');
        $m=M('auth_group');
        //获取所有规则id
        $ruleID=$m->field('rules')->where($where)->select();
        $model=M('auth_rule');
        $mid=$model->alias('ar')->join('LEFT JOIN dsy_auth_modules as mo on ar.mid = mo.id')
            ->field('ar.mid,mo.moduleName')->group('ar.mid')->select();
        foreach ($mid as $v) {
            $map['mid']=array('in',$v['mid']);
            $result[$v['moduleName']]=$model->where($map)->select();
        }
        $this->group=$group;
        $this->result=$result;
        $this->ruleID=$ruleID;
        $this->display();
    }

    /*更新角色授权
    * by chen
    */
    public function accessSelectHandle($groupID='0'){
        if (IS_POST) {
            if($groupID == 1) $this->error('超级管理员拥有全部权限');
            $arr=I('rule');
            $_POST['rules']=implode(',',$arr);
            $m=M('auth_group');
            if (!$m->create($_POST,2)){
                $this->error($m->getError());
            }
            if (false!==$m->where('id='.$groupID)->save()) {
                $this->addLog('title='.$_POST['title'].'&id='.$groupID,1);// 记录操作日志
                $this->success('权限更新成功', U('Admin/Auth/groupList'));
            } else {
                $this->addLog('title='.$_POST['title'].'&id='.$groupID,0);// 记录操作日志
                $this->error('权限更新失败');
            }
        } else {
            $this->error('访问错误');
        }
    }

    /*角色组成员列表
    * by chen
    */
    public function groupMember($n='15'){
        $group['id']=$where['ag.group_id']=I('id');
        $group['groupName']=I('name');
        $model=M('system_user');
        $count=$model->alias('mb')->join('LEFT JOIN dsy_auth_group_access as ag on mb.user_id = ag.uid')
            ->where($where)->count();
        $page=new \Think\Page($count, $n);
        $this->Page =$page->show();
        $result=$model->alias('mb')->join('LEFT JOIN dsy_auth_group_access as ag on mb.user_id = ag.uid')
            ->field('mb.user_id,mb.user_name,mb.status,ag.uid,ag.group_id')
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)->order('ag.uid desc')->select();
        $this->result=$result;
        $this->group=$group;
        $this->display();
    }

    /*删除角色组成员
     * by chen
    */
    public function groupMemberDel($uid){
        $AG = M('auth_group_access');
        $where['uid']=$uid;
        if ($AG->where($where)->delete()) {
            $this->addLog('uid='.$uid,1);// 记录操作日志
            $this->success('删除角色组成员成功');
        } else {
            $this->addLog('uid='.$uid,0);// 记录操作日志
            $this->error('删除角色组成员错误');
        }
    }

    /*权限列表
     * by chen
    */
    public function accessList($n=20){
        //过滤筛选条件
        $where = array();
        $sType= I('get.searchType');
        switch($sType)
        {
            case'module':
                $where['moduleName'] = array('like','%'. I('get.searchkey')."%");
                break;
            case'title':
                $where['title'] = array('like','%'. I('get.searchkey')."%");
                break;
            case'condition':
                $where['condition'] = array('like','%'.I('get.searchkey')."%");
                break;
            default:
                break;
        }

        $model=M('auth_rule');
        $count=$model->alias('ar')->join('LEFT JOIN dsy_auth_modules as mo on ar.mid = mo.id')
            ->where($where)->count();
        $Page = new \Think\Page($count, $n);
        $this->Page = $Page->show();
        $data=$model->alias('ar')->join('LEFT JOIN dsy_auth_modules as mo on ar.mid = mo.id')
            ->field('ar.rule_id,ar.name,ar.title,ar.type,ar.status,ar.condition,ar.mid,mo.moduleName')
            ->limit($Page->firstRow.','.$Page->listRows)->order('mo.moduleName desc')->where($where)->select();
        $this->list=$data;
        C('TOKEN_ON',false); //列表页关闭表单
        $this->display();
    }

    /*添加权限
     * by chen
    */
    public function accessAdd(){
        if (IS_POST) {
            $model=M("auth_rule");
            if (!$model->create($_POST,1)){
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($model->getError());
            }
            $lastId=$model->add();
            if($lastId){
                $this->addLog('name='.$_POST['name'].'&title='.$_POST['title'].'&rule_id='.$lastId,1);// 记录操作日志
                $this->success('添加权限成功', U('Admin/Auth/accessList'));
            }else{
                $this->addLog('name='.$_POST['name'].'&title='.$_POST['title'].'&rule_id='.$lastId,0);// 记录操作日志
                $this->error('添加权限失败');
            }
        }
        else
        {
            $m=M('auth_modules');
            $data=$m->select();
            $this->info=$data;
            $this->display();
        }
    }

    /*修改权限
     * by chen
    */
    public function accessEdit($rule_id='0'){
        if (IS_POST) {
            $AR = M('auth_rule');
            if (!$AR->create($_POST,2)){
                //如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($AR->getError());
            }
            if (false !== $AR->where('rule_id = ' . $rule_id)->save()) {
                $this->addLog('title='.$_POST['title'].'&rule_id='.$rule_id,1);// 记录操作日志
                $this->success('编辑权限成功', U('Admin/Auth/accessList'));
            } else {
                $this->addLog('title='.$_POST['title'].'&rule_id='.$rule_id,0);// 记录操作日志
                $this->error('编辑权限出错');
            }

        } else {
            $AR = M('auth_rule');
            if ($rule_id) {
                $info = $AR->find($rule_id);
                $this->info = $info;
            }
            //所属模块
            $m=M('auth_modules');
            $this->data=$m->select();
            $this->display();
        }
    }

    /*删除权限
    *by chen
    */
    public function accessDel($rule_id){
        $SS = M('auth_rule');
        if ($SS->delete($rule_id)) {
            $this->addLog('rule_id='.$rule_id,1);// 记录操作日志
            $this->success('权限删除成功', U('Admin/Auth/accessList'));
        } else {
            $this->addLog('rule_id='.$rule_id,0);// 记录操作日志
            $this->error('权限删除失败');
        }
    }

    /*模块列表
     * by chen
    */
    public function modulesList($n=20){
        //过滤筛选条件
        $where = array();
        $sType= I('get.searchType');
        switch($sType)
        {
            case'moduleName':
                $where['moduleName'] = array('like','%'. I('get.searchkey')."%");
                break;
            default:
                break;
        }
        $model=M('auth_modules');
        $count=$model->where($where)->count();
        $Page = new \Think\Page($count, $n);
        $this->Page = $Page->show();
        $data=$model->limit($Page->firstRow.','.$Page->listRows)->where($where)->select();
        $this->list=$data;
        $this->display();
    }

    /*添加模块
     * by chen
    */
    public function modulesAdd(){
        if (IS_POST) {
            $m=M("auth_modules");
            if (!$m->create($_POST,1)){
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($m->getError());
            }
            $lastId=$m->add();
            if($lastId){
                $this->addLog('moduleName='.I('moduleName').'&id='.$lastId,1);// 记录操作日志
                $this->success('添加模块成功', U('Admin/Auth/modulesList'));
            }else{
                $this->addLog('moduleName='.I('moduleName').'&id='.$lastId,0);// 记录操作日志
                $this->error('添加模块失败');
            }
        }
        else
        {
            $this->display();
        }
    }
    /*编辑模块
     * by chen
    */
    public function modulesEdit($id='0'){
        if (IS_POST) {
            $model = M('auth_modules');
            if (!$model->create($_POST,2)){
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($model->getError());
            }
            if (false !== $model->where('id = ' . $id)->save()) {
                $this->addLog('moduleName='.I('moduleName').'&id='.$id,1);// 记录操作日志
                $this->success('编辑模块成功', U('Admin/Auth/modulesList'));
            } else {
                $this->addLog('moduleName='.I('moduleName').'&id='.$id,0);// 记录操作日志
                $this->error('编辑模块出错');
            }

        } else {
            $m=M('auth_modules');
            $info = $m->find($id);
            $this->info=$info;
            $this->display();
        }
    }
    /*删除模块
    *by chen
    */
    public function modulesDel($id){
        $SS = M('auth_modules');
        $P = M('auth_rule');
        $count = $P->where('mid = '.$id)->count();
        if ($count){
            $this->addLog('id='.$id,0);// 记录操作日志
            $this->error('当前模块下面还有'.$count.'个权限。不可以删除！');
        }else{
            if ($SS->delete($id)) {
                $this->addLog('id='.$id,1);// 记录操作日志
                $this->success('模块删除成功', U('Admin/Auth/modulesList'));
            } else {
                $this->addLog('id='.$id,0);// 记录操作日志
                $this->error('模块删除失败');
            }
        }


    }

} 