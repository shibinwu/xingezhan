<?php
namespace Eshop\Controller;

use Common\Controller\AdminbaseController;
require_once SPAPP_PATH.'Core/Library/Vendor/PHPExcel/PHPExcel.php';
//import("Library.Vendor.PHPExcel.class.Reader.Excel5");

class IndexadminController extends AdminbaseController
{
    //当前使用语言 常量  LANG_SET 
    protected $posts_model;
    protected $product_order_model;
    protected $user_model;
    protected $yporder_model;
    protected $product_model;
    protected $zhanshou_model;
    protected $members_model;

    function _initialize()
    {
        parent::_initialize();
        $this->posts_model = M("Yaopin");
        $this->product_order_model = M("Zsorder");
        $this->user_model = M("Users");
        $this->yporder_model = M("Yaopin_order");
        $this->product_model = M("Product");
        $this->zhanshou_model = M("Zhanshou");
        $this->members_model = M("Members");
    }

    // 后台展售专题列表
    public function showindex()
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

        $count = $this->zhanshou_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->zhanshou_model
            ->where($where)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach ($list as $k => $val) {
            $list[$k]['names'] = $this->user_model->where(array('id' => $val['adduser']))->getField('user_nicename');
            $list[$k]['nums'] = $this->product_model->where(array('cid' => $val['id']))->count();
        }
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    // 后台展售目录添加
    public function showadd()
    {
        if (IS_POST) {
            $_POST['post']['small_tpic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['tpic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['adduser'] = get_current_admin_id();
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this->zhanshou_model->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }
        $this->display();
    }

    // 后台展售专题编辑
    public function showedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            $_POST['post']['small_tpic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['tpic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
//            $img = new \Think\Image(); //实例化
//            $img->open($_POST['post']['tpic']); //打开被处理的图片
//            $img->thumb(100,100); //制作缩略图(100*100)
//            $img->save($smallimg_path); //保存缩略图到服务器
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = $this->zhanshou_model->save($article);
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
        $info = $this->zhanshou_model->where($where)->find();
        $this->assign('post', $info);
        $this->display();
    }

    // 后台展售专题删除
    public function showdelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->zhanshou_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }

        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->zhanshou_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

    // 后台信鴿列表
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

        $count = $this->product_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->product_model
            ->where($where)
            ->order("id ASC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach ($list as $k => $val) {
            $list[$k]['types'] = $this->zhanshou_model->where(array('id' => $val['cid']))->getField('tname');
        }
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    // 后台信鸽添加
    public function xingeadd()
    {
        if (IS_POST) {
            $_POST['post']['small_pic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['pic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $_POST['post']['created_by'] = get_current_admin_id();
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = M("Product")->add($article);
            if ($result) {
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }
            exit;
        }

        $info = $this->zhanshou_model->getField('id,tname');

        $this->assign('post', $info);
        $this->display();
    }

    // 后台信鸽编辑
    public function xingeedit()
    {
        if (IS_POST) {
            $post_id = intval($_POST['post']['id']);
            $_POST['post']['small_pic'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $result = M("Product")->save($article);
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
        $info = $this-> product_model->where($where)->find();
        $data = $this-> zhanshou_model ->getField('id,tname');
        $this->assign('post', $info);
        $this->assign('data', $data);
        $this->display();
    }

    // 后台信鸽删除
    public function xingedelete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->product_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }

        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->product_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

    // 后台展售订单列表
    public function orderlist()
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
        $count = $this->product_order_model->where($where)->count();
        $page = $this->page($count, 20);
        $list = $this->product_order_model
            ->where($where)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach ($list as $k => $val) {
//            $list[$k]['names'] = $this->members_model->where(array('id' => $val['user_id']))->getField('user_nicename');
            $list[$k]['realname'] = $this->members_model->where(array('id' => $val['user_id']))->getField('realname');
            $list[$k]['huanhao'] = $this->product_model->where(array('id' => $val['shangping_id']))->getField('huanhao');
            $list[$k]['title'] = $this->product_model->where(array('id' => $val['shangping_id']))->getField('title');
        }
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    // 后台鸽子展售订单添加
    public function orderadd()
    {
        if (IS_POST) {
            $_POST['post']['adduser'] = get_current_admin_id();
            $article = I("post.post");
            //取出产品id和会员id
            $products = $this-> product_model-> getField('id',true);
            $members = $this->members_model -> getField('id',true);
            //判断输入的产品id和会员id是否存在
            if (in_array("$article[shangping_id]", $products))
            {
                if (in_array("$article[user_id]", $members)){
                    $result = M("Zsorder")->add($article);
                    if ($result) {
                        $_POST = array("id" => "$result");
                        $this->orderedit();
                    } else {
                        $this->error("添加失败！");
                    }
                    exit;
                }else{
                    echo "未找到该会员";
                    $this->display();
                    exit;
                }
            }
            else
            {
                echo "请输入正确的鸽子ID";
                $this->display();
                exit;
            }
        }
        $this->display();
    }
    // 后台处理订单信息，生成订单
    public function orderedit()
    {
        if (IS_POST && count($_POST) > 1) {
            $post_id = intval($_POST['post']['id']);
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $article['addtime'] = time();
            //删除原始的id
//            array_splice($article,0,1);
            $result = $this-> product_order_model->save($article);
            if ($result !== false) {
                //修改待处理订单的状态
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }
        $where = array();
        $id = I('post.id');
        $where['lanhai_zsorder.id'] = $id;
        $info = $this-> product_order_model
            ->where($where)
            ->join('lanhai_product pm ON lanhai_zsorder.shangping_id = pm.id')
            ->join('lanhai_members m ON lanhai_zsorder.user_id = m.id')
            ->field('lanhai_zsorder.id,lanhai_zsorder.remark,shangping_id,user_id,pm.price,pm.sequence,pm.title,huanhao,m.username,m.realname,m.address,m.email,m.telephone,m.mobile')
            ->select();
        foreach ($info as $key => $val){
            $info = $val;
        }
        $info['order_sn'] = $this->get_sn();
        $this->assign('post', $info);
        $this->display('orderedit');
    }

    // 后台药品列表
    public function index()
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
        $yaopin_model = M("Yaopin");


        $count = $yaopin_model->where($where)->count();
        $page = $this->page($count, 20);
        $usersMod = M('Users');
        $list = $yaopin_model
            ->where($where)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        foreach ($list as $k => $val) {
            $list[$k]['name'] = $usersMod->where(array('id' => $val['created_by']))->getField('user_nicename');
        }

        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    // 后台鸽子展售订单添加
    public function geyaoadd()
    {
        if (IS_POST) {
            $_POST['post']['adduser'] = get_current_admin_id();
            $article = I("post.post");
            //取出产品id和会员id
            $products = $this-> posts_model-> getField('id',true);
            $members = $this->members_model -> getField('id',true);
            //判断输入的产品id和会员id是否存在
            if (in_array("$article[shangping_id]", $products))
            {
                if (in_array("$article[user_id]", $members)){
                    $result = $this->yporder_model->add($article);
                    if ($result) {
                        $_POST = array("id" => "$result");
                        $this->geyaoedit();
                    } else {
                        $this->error("添加失败！");
                    }
                    exit;
                }else{
                    echo "未找到该会员";
                    $this->display();
                    exit;
                }
            }
            else
            {
                echo "请输入正确的鸽药ID";
                $this->display();
                exit;
            }
        }
        $this->display();
    }
    // 后台处理订单信息，生成订单
    public function geyaoedit()
    {
        if (IS_POST && count($_POST) > 1) {
            $post_id = intval($_POST['post']['id']);
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $article['addtime'] = time();
            //删除原始的id
//            array_splice($article,0,1);
            $result = $this-> yporder_model->save($article);
            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
            exit;
        }
        $where = array();
        $id = I('post.id');
        $where['lanhai_yaopin_order.id'] = $id;
        $info = $this-> yporder_model
            ->where($where)
            ->join('lanhai_yaopin y ON lanhai_yaopin_order.shangping_id = y.id')
            ->join('lanhai_members m ON lanhai_yaopin_order.user_id = m.id')
            ->field('lanhai_yaopin_order.id,lanhai_yaopin_order.remark,num,shangping_id,user_id,y.price,y.sequence,y.title,m.username,m.realname,m.address,m.email,m.telephone,m.mobile')
            ->select();
        foreach ($info as $key => $val){
            $info = $val;
        }
        $info['order_sn'] = $this->get_sn();
        $this->assign('post', $info);
        $this->display('geyaoedit');
    }


    // 药品删除
    public function delete()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id", 0, 'intval');
            if ($this->posts_model->where(array('id' => $id))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }

        if (isset($_POST['ids'])) {
            $ids = I('post.ids/a');

            if ($this->posts_model->where(array('id' => array('in', $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

    // 后台鴿藥訂單列表
    public function geyaoindex()
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
            $list[$k]['name'] = $this->posts_model->where(array('id' => $val['shangping_id']))->getField('title');
        }
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    // 导出展售列表
    public function daochu()
    {
        $objExcel = new \PHPExcel();
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
        $objExcel->getActiveSheet()->setCellValue('A1','id');
        $objExcel->getActiveSheet()->setCellValue('B1','tname');
        $objExcel->getActiveSheet()->setCellValue('C1','tpic');
        $driver = $this->zhanshou_model->select();

        $count = count($driver);//$driver 为数据库表取出的数据
            for ($i = 2; $i <= $count+1; $i++) {
             $objExcel->getActiveSheet()->setCellValue('A' . $i, $driver[$i-2]['id']);
            }
        for ($i = 2; $i <= $count+1; $i++) {
            $objExcel->getActiveSheet()->setCellValue('B' . $i, $driver[$i-2]['tname']);
        }
        $objExcel->setActiveSheetIndex();
        header('Content-Type: applicationnd.ms-excel');
        header('Content-Disposition: attachment;filename="test.xls"');
        header('Cache-Control: max-age=0');

        $objWriter->save('php://output');
        exit;
    }
    public function sendemail(){
        if (IS_POST) {
            $article = I("post.post");
            $article['content'] = htmlspecialchars_decode($article['content']);
            $address = $article['to'];
            $subject = $article['subject'];
            $message = $article['content'];
            $arr = sp_send_email($address,$subject,$message);
            if ($arr['error']) {
                $this->error("发送失败！");
            } else {
                $this->success("发送成功！");
            }
            exit;
        }
        $this->display();
    }


    //生成订单号
    public function get_sn() {
        return date('YmdHis').rand(100000, 999999);
    }
}
