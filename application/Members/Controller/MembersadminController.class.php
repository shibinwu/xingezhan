<?php
namespace Members\Controller;

use Common\Controller\AdminbaseController;

class MembersadminController extends AdminbaseController {
    //当前使用语言 常量  LANG_SET
    protected $members_model;
    protected $caiwu_model;
    protected $chongzhi_model;
    protected $fkdj_model;
    protected $bjst_model;
    function _initialize()
    {
        parent::_initialize();
        $this->members_model = M("Members");
        $this->caiwu_model = M("Caiwu");
        $this->chongzhi_model = M("Chongzhi");
        $this->fkdj_model = M("Fkdj");
        $this->bjst_model = M("Bjst");
    }
    
    // 后台本站会员列表
    public function membersindex(){
        $where=array();
        $request=I('request.');
        
        if(!empty($request['uid'])){
            $where['id']=intval($request['uid']);
        }
        
        if(!empty($request['keyword'])){
            $keyword=$request['keyword'];
            $keyword_complex=array();
            $keyword_complex['username']  = array('like', "%$keyword%");
            $keyword_complex['mobile']  = array('like',"%$keyword%");
            $keyword_complex['email']  = array('like',"%$keyword%");
            $keyword_complex['_logic'] = 'or';
            $where['_complex'] = $keyword_complex;
        }
        $where['l'] = LANG_SET;

    	
    	$count=$this->members_model->where($where)->count();
    	$page = $this->page($count, 20);
    	
    	$list = $this->members_model
    	->where($where)
    	->order("addtime DESC")
    	->limit($page->firstRow . ',' . $page->listRows)
    	->select();

    	
    	$this->assign('list', $list);
    	$this->assign("page", $page->show('Admin'));
    	
    	$this->display();
    }

    // 后台会员添加
    public function membersadd()
    {
        if (IS_POST) {
            $_POST['post']['pic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['created_by'] = get_current_admin_id();
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this->members_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }

        $this->display();
    }

    // 后台会员编辑
    public function membersedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            $_POST['post']['pic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            unset($_POST['post']['post_author']);
            $article = I("post.post");

            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this-> members_model->save($article);
            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }
        $where = array();
        $id = I('get.id');
        $where['id'] = $id;
        $info = $this-> members_model->where($where)->find();
        $this->assign('post', $info);
        $this->display();
    }

    // 后台会员删除
    public function membersdelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->members_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }

        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->members_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }
    // 后台财务列表
    public function caiwuindex(){
        $where=array();
        $request=I('request.');

        if(!empty($request['uid'])){
            $where['id']=intval($request['uid']);
        }

        if(!empty($request['keyword'])){
            $keyword=$request['keyword'];
            $keyword_complex=array();
            $keyword_complex['username']  = array('like', "%$keyword%");
            $keyword_complex['mobile']  = array('like',"%$keyword%");
            $keyword_complex['email']  = array('like',"%$keyword%");
            $keyword_complex['_logic'] = 'or';
            $where['_complex'] = $keyword_complex;
        }
        $where['l'] = LANG_SET;


        $count=$this->caiwu_model->where($where)->count();
        $page = $this->page($count, 20);

        $list = $this->caiwu_model
            ->where($where)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach ($list as $key => $val){
            $list[$key]['username'] = $this->members_model->where(array('id' => $val['uid']))->getField('username');
        }


        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    // 后台添加财务信息
    public function caiwuadd()
    {
        if (IS_POST) {
            $_POST['post']['created_by'] = get_current_admin_id();
            $article = I("post.post");

            $article['content'] = htmlspecialchars_decode($article['content']);
            $article['addtime'] = time();
            $result = $this->caiwu_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }

        $where = array();
        $id = I('get.id');
        $where['id'] = $id;
        $info = $this-> members_model->where($where)->find();

        $this->assign('post', $info);

        $this->display();

    }

    // 后台财务编辑信息
    public function caiwuedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            unset($_POST['post']['post_author']);
            $article = I("post.post");

            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this-> caiwu_model->save($article);
            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }
        $where = array();
        $id = I('get.id');
        $where['id'] = $id;
        $post = $this-> caiwu_model->where($where)->find();

            $post['username'] = $this-> members_model->where(array('id'=>$post['uid']))->getField('username');
            $post['baojin'] = $this-> members_model->where(array('id'=>$post['uid']))->getField('baojin');

        $this->assign('post', $post);
        $this->display();
    }

    // 后台财务信息删除
    public function caiwudelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->caiwu_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }

        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->caiwu_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

    // 后台充值列表
    public function chongzhiindex(){
        $where=array();
        $request=I('request.');

        if(!empty($request['uid'])){
            $where['id']=intval($request['uid']);
        }

        if(!empty($request['keyword'])){
            $keyword=$request['keyword'];
            $keyword_complex=array();
            $keyword_complex['v_oid']  = array('like',"%$keyword%");
            $keyword_complex['id']  = array('like',"%$keyword%");
            $keyword_complex['_logic'] = 'or';
            $where['_complex'] = $keyword_complex;
        }
        $where['l'] = LANG_SET;


        $count=$this->chongzhi_model->where($where)->count();
        $page = $this->page($count, 20);

        $list = $this->chongzhi_model
            ->where($where)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach ($list as $key => $val){
            $list[$key]['username'] = $this->members_model->where(array('id' => $val['uid']))->getField('username');
        }


        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    // 后台添加充值信息
    public function chongzhiadd()
    {
        if (IS_POST) {
            $_POST['post']['created_by'] = get_current_admin_id();
            $article = I("post.post");

            $article['content'] = htmlspecialchars_decode($article['content']);
            $article['addtime'] = time();
            $result = $this->caiwu_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }

        $where = array();
        $id = I('get.id');
        $where['id'] = $id;
        $info = $this-> members_model->where($where)->find();

        $this->assign('post', $info);

        $this->display();

    }

    // 后台充值编辑信息
    public function chongzhiedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            unset($_POST['post']['post_author']);
            $article = I("post.post");

            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this-> caiwu_model->save($article);
            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }
        $where = array();
        $id = I('get.id');
        $where['id'] = $id;
        $post = $this-> caiwu_model->where($where)->find();

        $post['username'] = $this-> members_model->where(array('id'=>$post['uid']))->getField('username');
        $post['baojin'] = $this-> members_model->where(array('id'=>$post['uid']))->getField('baojin');

        $this->assign('post', $post);
        $this->display();
    }

    // 后台充值信息删除
    public function chongzhidelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->caiwu_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }

        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->caiwu_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }


    // 后台付款登记列表
    public function fkdjindex(){
        $where=array();
        $request=I('request.');

        if(!empty($request['uid'])){
            $where['id']=intval($request['uid']);
        }

        if(!empty($request['keyword'])){
            $keyword=$request['keyword'];
            $keyword_complex=array();
            $keyword_complex['v_oid']  = array('like',"%$keyword%");
            $keyword_complex['id']  = array('like',"%$keyword%");
            $keyword_complex['_logic'] = 'or';
            $where['_complex'] = $keyword_complex;
        }
        $where['l'] = LANG_SET;


        $count=$this->fkdj_model->where($where)->count();
        $page = $this->page($count, 20);

        $list = $this->fkdj_model
            ->where($where)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach ($list as $key => $val){
            $list[$key]['username'] = $this->members_model->where(array('id' => $val['uid']))->getField('username');
        }

        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    // 后台添加付款登记
    public function fkdjadd()
    {
        if (IS_POST) {
            $_POST['post']['created_by'] = get_current_admin_id();
            $article = I("post.post");

            $article['content'] = htmlspecialchars_decode($article['content']);
            $article['addtime'] = time();
            $result = $this->caiwu_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }

        $where = array();
        $id = I('get.id');
        $where['id'] = $id;
        $info = $this-> members_model->where($where)->find();

        $this->assign('post', $info);

        $this->display();

    }

    // 后台编辑付款登记
    public function fkdjedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            unset($_POST['post']['post_author']);
            $article = I("post.post");

            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this-> fkdj_model->save($article);
            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }
        $where = array();
        $id = I('get.id');
        $where['id'] = $id;
        $post = $this-> fkdj_model->where($where)->find();

        $post['username'] = $this-> members_model->where(array('id'=>$post['uid']))->getField('username');

        $this->assign('post', $post);
        $this->display();
    }

    // 后台财务信息删除
    public function fkdjdelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->fkdj_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }

        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->fkdj_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

    // 后台保证金退回申请列表
    public function baojinindex(){
        $where=array();
        $request=I('request.');

        if(!empty($request['uid'])){
            $where['id']=intval($request['uid']);
        }
        if(!empty($request['keyword'])){
            $keyword=$request['keyword'];
            $keyword_complex=array();
            $keyword_complex['username']  = array('like', "%$keyword%");
            $keyword_complex['mobile']  = array('like',"%$keyword%");
            $keyword_complex['email']  = array('like',"%$keyword%");
            $keyword_complex['_logic'] = 'or';
            $where['_complex'] = $keyword_complex;
        }
        $where['l'] = LANG_SET;
        $count=$this->bjst_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->bjst_model
            ->where($where)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach ($list as $key => $val){
            $list[$key]['username'] = $this->members_model->where(array('id' => $val['uid']))->getField('username');
            $list[$key]['realname'] = $this->members_model->where(array('id' => $val['uid']))->getField('realname');
        }
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    // 前台添加保证金退回申请
    public function baojinadd()
    {
        if (IS_POST) {
            $_POST['post']['created_by'] = get_current_admin_id();
            $article = I("post.post");

            $article['content'] = htmlspecialchars_decode($article['content']);
            $article['addtime'] = time();
            $result = $this->caiwu_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }

        $where = array();
        $id = I('get.id');
        $where['id'] = $id;
        $info = $this-> members_model->where($where)->find();

        $this->assign('post', $info);

        $this->display();

    }

    // 后台保证金退回申请编辑
    public function baojinedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            unset($_POST['post']['post_author']);
            $article = I("post.post");

            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this-> bjst_model->save($article);
            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }
        $where = array();
        $id = I('get.id');
        $where['id'] = $id;
        $post = $this-> bjst_model->where($where)->find();

        $post['username'] = $this-> members_model->where(array('id'=>$post['uid']))->getField('username');
        $post['realname'] = $this-> members_model->where(array('id'=>$post['uid']))->getField('realname');
        $post['telephone'] = $this-> members_model->where(array('id'=>$post['uid']))->getField('telephone');
        $post['address'] = $this-> members_model->where(array('id'=>$post['uid']))->getField('address');

        $this->assign('post', $post);
        $this->display();
    }

    // 后台保证金退回申请记录删除
    public function baojindelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->bjst_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }

        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->bjst_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }
    // 后台本站用户禁用
    public function ban(){
    	$id= I('get.id',0,'intval');
    	if ($id) {
    		$result = M("Users")->where(array("id"=>$id,"user_type"=>2))->setField('user_status',0);
    		if ($result) {
    			$this->success("会员拉黑成功！", U("indexadmin/index"));
    		} else {
    			$this->error('会员拉黑失败,会员不存在,或者是管理员！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }
    
    // 后台本站用户启用
    public function cancelban(){
    	$id= I('get.id',0,'intval');
    	if ($id) {
    		$result = M("Users")->where(array("id"=>$id,"user_type"=>2))->setField('user_status',1);
    		if ($result) {
    			$this->success("会员启用成功！", U("indexadmin/index"));
    		} else {
    			$this->error('会员启用失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }
}
