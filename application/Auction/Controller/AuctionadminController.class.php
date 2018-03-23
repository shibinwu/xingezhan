<?php
namespace Auction\Controller;

use Common\Controller\AdminbaseController;

class AuctionadminController extends AdminbaseController
{
    //当前使用语言 常量  LANG_SET 
    protected $pmzt_model;
    protected $pmproduct_model;
    protected $member_model;
    protected $pmjilu_model;
    protected $pmorder_model;
    protected $user_model;
    protected $product_model;
    protected $zhanshou_model;

    function _initialize()
    {
        parent::_initialize();
        $this->pmzt_model = M("Pmzt");
        $this->pmproduct_model = M("Pmproduct");
        $this->member_model = M("Members");
        $this->pmjilu_model = M("Pmjilu");
        $this->pmorder_model = M("Pmorder");
        $this->user_model = M("Users");
        $this->product_model = M("Product");
        $this->zhanshou_model = M("Zhanshou");
    }

    // 后台拍卖专题列表
    public function auctionindex()
    {
        $where = array();
        $request = I('request.');

        if (($request['status'] == '0') || ($request['status'] == 1)) {
            $where['hiden'] = $request['status'];
        }
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $where['title'] = array('like', "%$keyword%");
        }
        $where['l'] = LANG_SET;

        $count = $this->pmzt_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->pmzt_model
            ->where($where)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $a = array();
        foreach ($list as $k => $val) {
            $list[$k]['names'] = $this->user_model->where(array('id' => $val['adduser']))->getField('user_nicename');
            $list[$k]['nums'] = $this->pmproduct_model->where(array('cid' => $val['id']))->count();
            $nums= $this->pmjilu_model->where(array('cid' => $val['id']))->getField('pmprice',true);
            $list[$k]['totals']=array_sum($nums);

        }

        $data = $this->pmproduct_model->select();

        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    // 后台拍卖专题添加
    public function auctionadd()
    {
        if (IS_POST) {
            $_POST['post']['pics'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['adduser'] = get_current_admin_id();
//            $_POST['post']['country'] = implode(",",array($_POST['post']['country1'],$_POST['post']['country2'],$_POST['post']['country3']));
            $_POST['post']['country'] = implode(",",array_filter(array($_POST['post']['country1'],$_POST['post']['country2'],$_POST['post']['country3'])));
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this->pmzt_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }
        $this->display();
    }

    // 后台拍卖专题编辑
    public function auctionedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            $_POST['post']['pics'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['country'] = implode(",",array_filter(array($_POST['post']['country1'],$_POST['post']['country2'],$_POST['post']['country3'])));
//            $img = new \Think\Image(); //实例化
//            $img->open($_POST['post']['tpic']); //打开被处理的图片
//            $img->thumb(100,100); //制作缩略图(100*100)
//            $img->save($smallimg_path); //保存缩略图到服务器
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this->pmzt_model->save($article);
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
        $info = $this->pmzt_model->where($where)->find();
        $a = $info['country'];
        $arr = explode(",",$a);
        foreach ($arr as $k => $val) {
           if($val == 1){
               $info['country1'] =1;
           }elseif($val == 2){
               $info['country2'] =2;
           }else{
               $info['country3'] =3;
           }

        }
        $this->assign('post', $info);
        $this->display();
    }

    // 后台展售专题删除
    public function auctiondelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->pmzt_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }

        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->pmzt_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

    // 后台拍卖管理列表
    public function xingeindex()
    {
        $where = array();
        $request = I('request.');
        if (($request['status'] == '0') || ($request['status'] == 1)) {
            $where['hiden'] = $request['status'];
        }
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $where['title'] = array('like', "%$keyword%");
        }
        $where['l'] = LANG_SET;

        $count = $this->pmproduct_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->pmproduct_model
            ->where($where)
            ->order("id ASC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach ($list as $k => $val) {
            $list[$k]['types'] = $this->pmzt_model->where(array('id' => $val['cid']))->getField('tname');
//            $list[$k]['member'] = $this->pmjilu_model->where(array('gid' => $val['id']))->field('uid,username,pmprice')->select();
            $temp = $this->pmjilu_model->where(array('gid' => $val['id']))->field('uid,username,pmprice')->select();
            foreach ($temp as $k => $val){
                $list[$k]['uid'] = $val['uid'];
                $list[$k]['username'] = $val['username'];
                $list[$k]['pmprice'] = $val['pmprice'];
                $temps = $this->member_model->where(array('id' => $val['uid']))->field('baojin,realname')->select();
                foreach ($temps as $k => $val){
                    $list[$k]['baojin'] = $val['baojin'];
                    $list[$k]['realname'] = $val['realname'];
                }
            }
//            $sql = " select uid,username,pmprice from cms_pmjilu where gid= ".$val['id']." order by id desc limit 1 ";
        }
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    // 后台拍卖信鸽添加
    public function xingeadd()
    {
        if (IS_POST) {
            $_POST['post']['pic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['created_by'] = get_current_admin_id();
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = M("Pmproduct")->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }

        $info = $this->pmzt_model->getField('id,tname');

        $this->assign('post', $info);
        $this->display();
    }

    // 后台拍卖信鸽编辑
    public function xingeedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            $_POST['post']['pic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            if(!isset($article[tuijian])){
                $article[tuijian] = 0;
            }elseif(!isset($article[zhiding])){
                $article[zhiding] = 1;
            }elseif(!isset($article[fobidden])){
                $article[fobidden] = 0;
            }
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this-> pmproduct_model->save($article);
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
        $info = $this-> pmproduct_model->where($where)->find();
        $data = $this-> pmzt_model ->getField('id,tname');
        $this->assign('post', $info);
        $this->assign('data', $data);
        $this->display();
    }

    // 后台拍卖信鸽删除
    public function xingedelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->pmproduct_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }

        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->pmproduct_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

    // 后台拍卖专题订单列表
    public function orderindex()
    {
        $where = array();
        $request = I('request.');

        if (($request['status'] == '0') || ($request['status'] == 1)) {
            $where['hiden'] = $request['status'];
        }
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $where['title'] = array('like', "%$keyword%");
        }
        //获取当前时间
        $temp = time();
        $where['l'] = LANG_SET;
        //拍卖已经结束的条件
        $where['end_time'] = array('lt',"$temp");
        $count = $this->pmzt_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->pmzt_model
            ->where($where)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach ($list as $k => $val) {
            $list[$k]['names'] = $this->user_model->where(array('id' => $val['adduser']))->getField('user_nicename');
            //计算同个专题的鸽子总数
            $list[$k]['nums'] = $this->pmproduct_model->where(array('cid' => $val['id']))->count();
            //计算同个专题所有鸽子的单价总和
            $nums = $this->pmorder_model->where(array('cid' => $val['id']))->getField('price',true);
            $list[$k]['price']=array_sum($nums);
            //计算同个专题所有鸽子的总金额总和
            $numss = $this->pmorder_model->where(array('cid' => $val['id']))->getField('totalprice',true);
            $list[$k]['totalprice']=array_sum($numss);
            $list[$k]['time'] = time();
        }
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    // 后台拍卖专题订单列表
    public function pmorderindex()
    {
        $where = array();
        $request = I('request.');

        if (($request['status'] == '0') || ($request['status'] == 1)) {
            $where['hiden'] = $request['status'];
        }
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $where['title'] = array('like', "%$keyword%");
        }
        $where['l'] = LANG_SET;
        $count = $this->pmorder_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->pmorder_model
            ->where($where)
            ->order("id ASC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach ($list as $k => $val) {
            $list[$k]['koop'] = $this->pmproduct_model->where(array('id' => $val['shangping_id']))->getField('sequence');
            $list[$k]['huanhao'] = $this->pmproduct_model->where(array('id' => $val['shangping_id']))->getField('huanhao');
            $list[$k]['auctionname'] = $this->pmzt_model->where(array('id' => $val['cid']))->getField('tname');
        }
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    // 后台查看订单信息
    public function pmorderedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this-> pmorder_model->save($article);
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
        $info = $this-> pmorder_model->where($where)->find();
//        $data = $this-> pmorder_model ->getField('id,tname');
        $this->assign('post', $info);
//        $this->assign('data', $data);
        $this->display();
    }

    // 后台待处理订单列表
    public function pendingorderindex()
    {
        $where = array();
        $request = I('request.');

        if (($request['status'] == '0') || ($request['status'] == 1)) {
            $where['hiden'] = $request['status'];
        }
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $where['title'] = array('like', "%$keyword%");
        }
        $where['l'] = LANG_SET;
        //取出当前出价最高纪录
        $where['status'] = array('eq',2);
        $count = $this->pmjilu_model->where($where)->count();
        $page = $this->page($count, 10);
        $list = $this->pmjilu_model
            ->where($where)
            ->order("id ASC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->getField('id,cid,sequence,gid,uid,username,cn_tname,cn_title,pmprice');
        foreach ($list as $k => $val) {
            $list[$k]['huanhao'] = $this->pmproduct_model->where(array('id' => $val['gid']))->getField('huanhao');
        }
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    // 后台处理订单信息，生成订单
    public function pendingorderedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            //删除原始的id
            array_splice($article,0,1);

            $article['order_sn']  = $this->get_sn();
            echo "<pre>";
            print_r($article);
            die;
            $result = $this-> pmorder_model->add($article);
            if ($result !== false) {
                //修改待处理订单的状态
                $this-> pmjilu_model->where($post_id)->setField('status',1);
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }
        $where = array();
        $id = I('get.id');
        $where['lanhai_pmjilu.id'] = $id;
        $info = $this-> pmjilu_model
                ->where($where)
                ->join('lanhai_pmproduct pm ON lanhai_pmjilu.gid = pm.id')
                ->join('lanhai_members m ON lanhai_pmjilu.uid = m.id')
                ->field('lanhai_pmjilu.id,gid,uid,pmprice,pm.sequence,pm.title,huanhao,m.username,realname,address,email,telephone,mobile')
                ->select();
//        echo "<a href= >提交</a>";
//
//        echo "<pre>";
//        print_r($info);
//        die;


//        $data = $this-> pmorder_model ->getField('id,tname');
        $this->assign('post', $info);
//        $this->assign('data', $data);
        $this->display();
    }

    // 后台拍卖历史列表
    public function historyindex()
    {
        $where = array();
        $request = I('request.');
        if (($request['status'] == '0') || ($request['status'] == 1)) {
            $where['hiden'] = $request['status'];
        }
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $where['title'] = array('like', "%$keyword%");
        }
        $where['l'] = LANG_SET;

        $count = $this->yporder_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->yporder_model
            ->where($where)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach ($list as $k => $val) {
            $list[$k]['name'] = $this->pmproduct_model->where(array('id' => $val['shangping_id']))->getField('title');
        }
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }
    //生成订单号
    public function get_sn() {
        return date('YmdHis').rand(100000, 999999);
    }
}
