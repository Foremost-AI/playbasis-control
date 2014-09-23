<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/MY_Controller.php';
class Goods extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('User_model');
        $this->load->model('Plan_model');
        $this->load->model('Permission_model');
        if(!$this->User_model->isLogged()){
            redirect('/login', 'refresh');
        }

        $this->load->model('Goods_model');

        $lang = get_lang($this->session, $this->config);
        $this->lang->load($lang['name'], $lang['folder']);
        $this->lang->load("goods", $lang['folder']);
    }

    public function index() {

        if(!$this->validateAccess()){
            echo "<script>alert('".$this->lang->line('error_access')."'); history.go(-1);</script>";
        }

        $this->data['meta_description'] = $this->lang->line('meta_description');
        $this->data['title'] = $this->lang->line('title');
        $this->data['heading_title'] = $this->lang->line('heading_title');
        $this->data['text_no_results'] = $this->lang->line('text_no_results');

        $this->getList(0);

    }

    public function page($offset=0) {

        if(!$this->validateAccess()){
            echo "<script>alert('".$this->lang->line('error_access')."'); history.go(-1);</script>";
        }

        $this->data['meta_description'] = $this->lang->line('meta_description');
        $this->data['title'] = $this->lang->line('title');
        $this->data['heading_title'] = $this->lang->line('heading_title');
        $this->data['text_no_results'] = $this->lang->line('text_no_results');

        $this->getList($offset);

    }

    public function import() {

        // Get Usage
        $site_id = $this->User_model->getSiteId();
        $usage = $this->Goods_model->getTotalGoodsBySiteId(
            array('site_id' => $site_id));
        $plan_id = $this->Permission_model->getPermissionBySiteId($site_id);

        // Get Limit
        $limit = $this->Plan_model->getPlanLimitById($plan_id, 'others', 'goods');

        if ($limit && $usage >= $limit) {
            $this->data['message'] = $this->lang->line('error_limit');
        }

        $this->data['meta_description'] = $this->lang->line('meta_description');
        $this->data['title'] = $this->lang->line('title');
        $this->data['heading_title'] = $this->lang->line('heading_title');
        $this->data['text_no_results'] = $this->lang->line('text_no_results');
        $this->data['form'] = 'goods/import';

        $this->form_validation->set_rules('name', $this->lang->line('entry_group'), 'trim|required|min_length[2]|max_length[255]|xss_clean');
        $this->form_validation->set_rules('reward_point', $this->lang->line('entry_point'), 'is_numeric|trim|xss_clean|greater_than[-1]|less_than[2147483647]');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $this->data['message'] = null;

            if (!$this->validateModify()) {
                $this->data['message'] = $this->lang->line('error_permission');
            }

            if ($limit && $usage >= $limit) {
                $this->data['message'] = $this->lang->line('error_limit');
            }

            if (empty($_FILES) || !isset($_FILES['file']['tmp_name'])) {
                $this->data['message'] = $this->lang->line('error_file');
            }

            $point_empty = true;
            $badge_empty = true;
            $custom_empty = true;
            $redeem = array();

            if($this->input->post('reward_point') != '' || (int)$this->input->post('reward_point') != 0){
                $point_empty = false;
                $redeem['point'] = array('point_value'=>(int)$this->input->post('reward_point'));
            }

            if($this->input->post('reward_badge')){
                foreach($this->input->post('reward_badge') as $rbk => $rb){
                    if($rb != '' || $rb != 0){
                        $badge_empty = false;
                        $redeem['badge'][$rbk] = (int)$rb;
                    }
                }
            }

            if($this->input->post('reward_reward')){
                foreach($this->input->post('reward_reward') as $rbk => $rb){
                    if($rb != '' || $rb != 0){
                        $custom_empty = false;
                        $redeem['custom'][$rbk] = (int)$rb;
                    }
                }
            }

            if($point_empty && $badge_empty && $custom_empty){
                $this->data['message'] = $this->lang->line('error_redeem');
            }

            if($this->form_validation->run() && $this->data['message'] == null){

                if($this->User_model->getClientId()){

                    $handle = fopen($_FILES['file']['tmp_name'], "r");
                    if ($handle) {
                        $this->addGoods($handle, $this->input->post(), $redeem, array($this->User_model->getClientId()), array($this->User_model->getSiteId()));
                    } else {
                        log_message('error', 'Cannot open file = '.$_FILES['file']['tmp_name']);
                    }
                    fclose($handle);

                    $this->session->set_flashdata('success', $this->lang->line('text_success'));

                    redirect('/goods', 'refresh');
                }else{

                    $this->load->model('Client_model');
                    $goods_data = $this->input->post();

                    if(isset($goods_data['admin_client_id']) && $goods_data['admin_client_id'] != 'all_clients'){

                        $clients_sites = $this->Client_model->getSitesByClientId($goods_data['admin_client_id']);
                        $list_site_id = array();
                        foreach ($clients_sites as $client){
                            array_push($list_site_id, $client['_id']);
                        }

                        $handle = fopen($_FILES['file']['tmp_name'], "r");
                        if ($handle) {
                            $this->addGoods($handle, $this->input->post(), $redeem, array(new MongoId($goods_data['admin_client_id'])), $list_site_id);
                        } else {
                            log_message('error', 'Cannot open file = '.$_FILES['file']['tmp_name']);
                        }
                        fclose($handle);

                    }else{
                        $all_sites_clients = $this->Client_model->getAllSitesFromAllClients();
                        $hash_client_id = array();
                        $list_site_id = array();
                        foreach($all_sites_clients as $site){
                            $hash_client_id[$site['client_id']] = true;
                            array_push($list_site_id, $site['_id']);
                        }

                        $handle = fopen($_FILES['file']['tmp_name'], "r");
                        if ($handle) {
                            $this->addGoods($handle, $this->input->post(), $redeem, array_keys($hash_client_id), array($list_site_id));
                        } else {
                            log_message('error', 'Cannot open file = '.$_FILES['file']['tmp_name']);
                        }
                        fclose($handle);
                    }
                    redirect('/goods', 'refresh');
                }
            }
        }
        $this->getForm(null, true);
    }

    public function insert() {

        // Get Usage
        $site_id = $this->User_model->getSiteId();
        $usage = $this->Goods_model->getTotalGoodsBySiteId(
            array('site_id' => $site_id));
        $plan_id = $this->Permission_model->getPermissionBySiteId($site_id);

        // Get Limit
        $limit = $this->Plan_model->getPlanLimitById($plan_id, 'others', 'goods');

        if ($limit && $usage >= $limit) {
            $this->data['message'] = $this->lang->line('error_limit');
        }

        $this->data['meta_description'] = $this->lang->line('meta_description');
        $this->data['title'] = $this->lang->line('title');
        $this->data['heading_title'] = $this->lang->line('heading_title');
        $this->data['text_no_results'] = $this->lang->line('text_no_results');
        $this->data['form'] = 'goods/insert';

        $this->form_validation->set_rules('name', $this->lang->line('entry_name'), 'trim|required|min_length[2]|max_length[255]|xss_clean');
        $this->form_validation->set_rules('reward_point', $this->lang->line('entry_point'), 'is_numeric|trim|xss_clean|greater_than[-1]|less_than[2147483647]');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $this->data['message'] = null;

            if (!$this->validateModify()) {
                $this->data['message'] = $this->lang->line('error_permission');
            }

            if ($limit && $usage >= $limit) {
                $this->data['message'] = $this->lang->line('error_limit');
            }

            $point_empty = true;
            $badge_empty = true;
            $custom_empty = true;
            $redeem = array();

            if($this->input->post('reward_point') != '' || (int)$this->input->post('reward_point') != 0){
                $point_empty = false;
                $redeem['point'] = array('point_value'=>(int)$this->input->post('reward_point'));
            }

            if($this->input->post('reward_badge')){
                foreach($this->input->post('reward_badge') as $rbk => $rb){
                    if($rb != '' || $rb != 0){
                        $badge_empty = false;
                        $redeem['badge'][$rbk] = (int)$rb;
                    }
                }
            }

            if($this->input->post('reward_reward')){
                foreach($this->input->post('reward_reward') as $rbk => $rb){
                    if($rb != '' || $rb != 0){
                        $custom_empty = false;
                        $redeem['custom'][$rbk] = (int)$rb;
                    }
                }
            }

            if($point_empty && $badge_empty && $custom_empty){
                $this->data['message'] = $this->lang->line('error_redeem');
            }

            $goods_data = $this->input->post();
            $goods_data['redeem'] = $redeem;

            if($this->form_validation->run() && $this->data['message'] == null){

                if($this->User_model->getClientId()){

                    $goods_id = $this->Goods_model->addGoods($goods_data);

                    $goods_data['goods_id'] = $goods_id;
                    $goods_data['client_id'] = $this->User_model->getClientId();
                    $goods_data['site_id'] = $this->User_model->getSiteId();

                    $this->Goods_model->addGoodsToClient($goods_data);

                    $this->session->set_flashdata('success', $this->lang->line('text_success'));

                    redirect('/goods', 'refresh');
                }else{

                    $this->load->model('Client_model');

                    if(isset($goods_data['sponsor'])){
                        $goods_data['sponsor'] = true;
                    }else{
                        $goods_data['sponsor'] = false;
                    }

                    if(isset($goods_data['admin_client_id']) && $goods_data['admin_client_id'] != 'all_clients'){

                        $clients_sites = $this->Client_model->getSitesByClientId($goods_data['admin_client_id']);

                        $goods_data['goods_id'] = $this->Goods_model->addGoods($goods_data);

                        $goods_data['client_id'] = $goods_data['admin_client_id'];

                        foreach ($clients_sites as $client){
                            $goods_data['site_id'] = $client['_id'];
                            $this->Goods_model->addGoodsToClient($goods_data);
                        }
                    }else{
                        $goods_data['goods_id'] = $this->Goods_model->addGoods($goods_data);

                        $all_sites_clients = $this->Client_model->getAllSitesFromAllClients();

                        foreach($all_sites_clients as $site){
                            $goods_data['site_id'] = $site['_id'];
                            $goods_data['client_id'] = $site['client_id'];
                            $this->Goods_model->addGoodsToClient($goods_data);
                        }
                    }
                    redirect('/goods', 'refresh');
                }
            }
        }
        $this->getForm();
    }

    public function update($goods_id) {
        $this->data['meta_description'] = $this->lang->line('meta_description');
        $this->data['title'] = $this->lang->line('title');
        $this->data['heading_title'] = $this->lang->line('heading_title');
        $this->data['text_no_results'] = $this->lang->line('text_no_results');
        $this->data['form'] = 'goods/update/'.$goods_id;

        $this->form_validation->set_rules('name', $this->lang->line('entry_name'), 'trim|required|min_length[2]|max_length[255]|xss_clean');
        $this->form_validation->set_rules('reward_point', $this->lang->line('entry_point'), 'is_numeric|trim|xss_clean|greater_than[-1]|less_than[2147483647]|');

        if (($_SERVER['REQUEST_METHOD'] === 'POST') && $this->checkOwnerGoods($goods_id)) {

            $this->data['message'] = null;

            if (!$this->validateModify()) {
                $this->data['message'] = $this->lang->line('error_permission');
            }

            $point_empty = true;
            $badge_empty = true;
            $custom_empty = true;
            $redeem = array();

            if($this->input->post('reward_point') != '' || (int)$this->input->post('reward_point') != 0){
                $point_empty = false;
                $redeem['point'] = array('point_value'=>(int)$this->input->post('reward_point'));
            }

            if($this->input->post('reward_badge')){
                foreach($this->input->post('reward_badge') as $rbk => $rb){
                    if($rb != '' || $rb != 0){
                        $badge_empty = false;
                        $redeem['badge'][$rbk] = (int)$rb;
                    }
                }
            }

            if($this->input->post('reward_reward')){
                foreach($this->input->post('reward_reward') as $rbk => $rb){
                    if($rb != '' || $rb != 0){
                        $custom_empty = false;
                        $redeem['custom'][$rbk] = (int)$rb;
                    }
                }
            }

            if($point_empty && $badge_empty && $custom_empty){
                $this->data['message'] = $this->lang->line('error_redeem');
            }

            $goods_data = $this->input->post();
            $goods_data['redeem'] = $redeem;

            if($this->form_validation->run() && $this->data['message'] == null){
                if($this->User_model->getClientId()){

                    if(!$this->Goods_model->checkGoodsIsSponsor($goods_id)){
                        $goods_data['client_id'] = $this->User_model->getClientId();
                        $goods_data['site_id'] = $this->User_model->getSiteId();
                        $this->Goods_model->editGoodsToClient($goods_id, $goods_data);
                    }else{
                        redirect('/goods', 'refresh');
                    }
                }else{
                    $this->Goods_model->editGoods($goods_id, $goods_data);

                    $this->Goods_model->editGoodsToClientFromAdmin($goods_id, $goods_data);
                }

                $this->session->set_flashdata('success', $this->lang->line('text_success_update'));

                redirect('/goods', 'refresh');
            }
        }

        $this->getForm($goods_id);
    }

    public function delete() {

        $this->data['meta_description'] = $this->lang->line('meta_description');
        $this->data['title'] = $this->lang->line('title');
        $this->data['heading_title'] = $this->lang->line('heading_title');
        $this->data['text_no_results'] = $this->lang->line('text_no_results');

        $this->error['warning'] = null;

        if(!$this->validateModify()){
            $this->error['warning'] = $this->lang->line('error_permission');
        }

        if ($this->input->post('selected') && $this->error['warning'] == null) {
            foreach ($this->input->post('selected') as $goods_id) {
                if($this->checkOwnerGoods($goods_id)){

                    if($this->User_model->getClientId()){
                        if(!$this->Goods_model->checkGoodsIsSponsor($goods_id)){
                            $this->Goods_model->deleteGoodsClient($goods_id);
                        }else{
                            redirect('/goods', 'refresh');
                        }
                    }else{
                        $this->Goods_model->deleteGoods($goods_id);
                        $this->Goods_model->deleteGoodsClientFromAdmin($goods_id);
                    }

                }
            }

            $this->session->set_flashdata('success', $this->lang->line('text_success_delete'));
            redirect('/goods', 'refresh');
        }

        $this->getList(0);
    }

    private function getList($offset) {
        $this->_getList($offset);
        $this->load->vars($this->data);
        $this->render_page('template');
    }

    public function getListForAjax($offset) {
        $this->_getList($offset);
        $this->load->vars($this->data);
        $this->render_page('goods_ajax');
    }

    private function _getList($offset, $per_page=10) {

        $this->load->library('pagination');

        $config['base_url'] = site_url('goods/page');

        $this->load->model('Image_model');

        $site_id = $this->User_model->getSiteId();
        $setting_group_id = $this->User_model->getAdminGroupID();

        $this->data['goods_list'] = array();
        $this->data['user_group_id'] = $this->User_model->getUserGroupId();
        $slot_total = 0;
        $this->data['slots'] = $slot_total;

        if ($this->User_model->getUserGroupId() == $setting_group_id) {
            $data['limit'] = $per_page;
            $data['start'] = $offset;
            $data['sort'] = 'sort_order';

            $results = $this->Goods_model->getGoodsList($data);

            $goods_total = $this->Goods_model->getTotalGoods($data);

            foreach ($results as $result) {

                if (isset($result['image'])){
                    $info = pathinfo($result['image']);
                    if(isset($info['extension'])){
                        $extension = $info['extension'];
                        $new_image = 'cache/' . utf8_substr($result['image'], 0, utf8_strrpos($result['image'], '.')).'-50x50.'.$extension;
                        $image = S3_IMAGE.$new_image;
                    }else{
                        $image = S3_IMAGE."cache/no_image-50x50.jpg";
                    }
                }else{
                    $image = S3_IMAGE."cache/no_image-50x50.jpg";
                }

                /*if ($result['image'] && (S3_IMAGE . $result['image'] != 'HTTP/1.1 404 Not Found' && S3_IMAGE . $result['image'] != 'HTTP/1.0 403 Forbidden')) {
                    $image = $this->Image_model->resize($result['image'], 50, 50);
                } else {
                    $image = $this->Image_model->resize('no_image.jpg', 50, 50);
                }*/
                $goodsIsPublic = $this->checkGoodsIsPublic($result['_id']);
                $this->data['goods_list'][] = array(
                    'goods_id' => $result['_id'],
                    'name' => $result['name'],
                    'quantity' => $result['quantity'],
                    'per_user' => $result['per_user'],
                    'status' => $result['status'],
                    'image' => $image,
                    'sort_order'  => $result['sort_order'],
                    'selected' => ($this->input->post('selected') && in_array($result['_id'], $this->input->post('selected'))),
                    'is_public'=>$goodsIsPublic
                );
            }
        }else{
            $groups = $this->Goods_model->getGroup($this->session->userdata('site_id'));
            $ids = array();
            $group_name = array();
            foreach ($groups as $group => $_ids) {
                $_id = array_shift($_ids); // skip first one
                $group_name[$_id->{'$id'}] = $group;
                $ids = array_merge($ids, $_ids);
            }
            $goods_total = $this->Goods_model->getTotalGoodsBySiteId(array('site_id' => $site_id, 'sort' => 'sort_order', '$nin' => $ids));
            $goods_list = $this->Goods_model->getGoodsBySiteId(array('site_id' => $site_id, 'limit' => $per_page, 'start' => $offset, 'sort' => 'sort_order', '$nin' => $ids));

            $this->data['no_image'] = S3_IMAGE."cache/no_image-50x50.jpg";

            foreach ($goods_list as $goods) {

                if (isset($goods['image'])){
                    $info = pathinfo($goods['image']);
                    if(isset($info['extension'])){
                        $extension = $info['extension'];
                        $new_image = 'cache/' . utf8_substr($goods['image'], 0, utf8_strrpos($goods['image'], '.')).'-50x50.'.$extension;
                        $image = S3_IMAGE.$new_image;
                    }else{
                        $image = S3_IMAGE."cache/no_image-50x50.jpg";
                    }
                }else{
                    $image = S3_IMAGE."cache/no_image-50x50.jpg";
                }
                /*if ($goods['image'] && (S3_IMAGE . $goods['image'] != 'HTTP/1.1 404 Not Found' && S3_IMAGE . $goods['image'] != 'HTTP/1.0 403 Forbidden')) {
                    $image = $this->Image_model->resize($goods['image'], 50, 50);
                }
                else {
                    $image = $this->Image_model->resize('no_image.jpg', 50, 50);
                }*/

                $_id = $goods['_id']->{'$id'};
                $this->data['goods_list'][] = array(
                    'goods_id' => $goods['_id'],
                    'name' => array_key_exists($_id, $group_name) ? $group_name[$_id] : $goods['name'],
                    'quantity' => $goods['quantity'],
                    'per_user' => $goods['per_user'],
                    'status' => $goods['status'],
                    'image' => $image,
                    'sort_order'  => $goods['sort_order'],
                    'selected' => ($this->input->post('selected') && in_array($goods['_id'], $this->input->post('selected'))),
                    'sponsor' => isset($goods['sponsor'])?$goods['sponsor']:null
                );
            }
        }

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        $config['total_rows'] = $goods_total;
        $config['per_page'] = $per_page;
        $config["uri_segment"] = 3;
        $choice = $config["total_rows"] / $config["per_page"];
        $config['num_links'] = round($choice);

        $config['next_link'] = 'Next';
        $config['next_tag_open'] = "<li class='page_index_nav next'>";
        $config['next_tag_close'] = "</li>";

        $config['prev_link'] = 'Prev';
        $config['prev_tag_open'] = "<li class='page_index_nav prev'>";
        $config['prev_tag_close'] = "</li>";

        $config['num_tag_open'] = '<li class="page_index_number">';
        $config['num_tag_close'] = '</li>';

        $config['cur_tag_open'] = '<li class="page_index_number active"><a>';
        $config['cur_tag_close'] = '</a></li>';

        $this->pagination->initialize($config);

        $this->data['pagination_links'] = $this->pagination->create_links();

        $this->data['main'] = 'goods';
        $this->data['setting_group_id'] = $setting_group_id;
    }

    private function getForm($goods_id=null, $import=false) {

        $this->load->model('Image_model');
        $this->load->model('Badge_model');
        $this->load->model('Reward_model');

        if (isset($goods_id) && ($goods_id != 0)) {
            if($this->User_model->getClientId()){
                $goods_info = $this->Goods_model->getGoodsToClient($goods_id);
            }else{
                $goods_info = $this->Goods_model->getGoods($goods_id);
            }
        }

        if ($this->input->post('name')) {
            $this->data['name'] = $this->input->post('name');
        } elseif (isset($goods_id) && ($goods_id != 0)) {
            $this->data['name'] = $goods_info['name'];
        } else {
            $this->data['name'] = '';
        }

        if ($this->input->post('description')) {
            $this->data['description'] = htmlentities($this->input->post('description'));
        } elseif (isset($goods_id) && ($goods_id != 0)) {
            $this->data['description'] = htmlentities($goods_info['description']);
        } else {
            $this->data['description'] = '';
        }

        if ($this->input->post('image')) {
            $this->data['image'] = $this->input->post('image');
        } elseif (!empty($goods_info)) {
            $this->data['image'] = $goods_info['image'];
        } else {
            $this->data['image'] = 'no_image.jpg';
        }

        if ($this->data['image']){
            $info = pathinfo($this->data['image']);
            if(isset($info['extension'])){
                $extension = $info['extension'];
                $new_image = 'cache/' . utf8_substr($this->data['image'], 0, utf8_strrpos($this->data['image'], '.')).'-100x100.'.$extension;
                $this->data['thumb'] = S3_IMAGE.$new_image;
            }else{
                $this->data['thumb'] = S3_IMAGE."cache/no_image-100x100.jpg";
            }
        }else{
            $this->data['thumb'] = S3_IMAGE."cache/no_image-100x100.jpg";
        }

        /*if ($this->input->post('image') && (S3_IMAGE . $this->input->post('image') != 'HTTP/1.1 404 Not Found' && S3_IMAGE . $this->input->post('image') != 'HTTP/1.0 403 Forbidden')) {
            $this->data['thumb'] = $this->Image_model->resize($this->input->post('image'), 100, 100);
        } elseif (!empty($goods_info) && $goods_info['image'] && (S3_IMAGE . $goods_info['image'] != 'HTTP/1.1 404 Not Found' && S3_IMAGE . $goods_info['image'] != 'HTTP/1.0 403 Forbidden')) {
            $this->data['thumb'] = $this->Image_model->resize($goods_info['image'], 100, 100);
        } else {
            $this->data['thumb'] = $this->Image_model->resize('no_image.jpg', 100, 100);
        }*/

        $this->data['no_image'] = S3_IMAGE."cache/no_image-100x100.jpg";

        if ($this->input->post('sort_order')) {
            $this->data['sort_order'] = $this->input->post('sort_order');
        } elseif (!empty($goods_info)) {
            $this->data['sort_order'] = $goods_info['sort_order'];
        } else {
            $this->data['sort_order'] = 0;
        }

        if ($this->input->post('status')) {
            $this->data['status'] = $this->input->post('status');
        } elseif (!empty($goods_info)) {
            $this->data['status'] = $goods_info['status'];
        } else {
            $this->data['status'] = 1;
        }

        if ($this->input->post('quantity')) {
            $this->data['quantity'] = $this->input->post('quantity');
        } elseif (!empty($goods_info)) {
            $this->data['quantity'] = $goods_info['quantity'];
        } else {
            $this->data['quantity'] = null;
        }

        if ($this->input->post('per_user')) {
            $this->data['per_user'] = $this->input->post('per_user');
        } elseif (!empty($goods_info)) {
            $this->data['per_user'] = $goods_info['per_user'];
        } else {
            $this->data['per_user'] = null;
        }

        if ($this->input->post('reward_point')) {
            $this->data['reward_point'] = $this->input->post('reward_point');
        } elseif (!empty($goods_info)) {
            $this->data['reward_point'] = isset($goods_info['redeem']['point']) ? $goods_info['redeem']['point']['point_value'] : 0;
        } else {
            $this->data['reward_point'] = 0;
        }

        if ($this->input->post('reward_badge')) {
            $this->data['reward_badge'] = $this->input->post('reward_badge');
        } elseif (!empty($goods_info)) {
            $this->data['reward_badge'] = isset($goods_info['redeem']['badge']) ? $goods_info['redeem']['badge'] : array();
        } else {
            $this->data['reward_badge'] = array();
        }

        if ($this->input->post('reward_reward')) {
            $this->data['reward_reward'] = $this->input->post('reward_reward');
        } elseif (!empty($goods_info)) {
            $this->data['reward_reward'] = isset($goods_info['redeem']['custom']) ? $goods_info['redeem']['custom'] : array();
        } else {
            $this->data['reward_reward'] = array();
        }

        if ($this->input->post('sponsor')) {
            $this->data['sponsor'] = $this->input->post('sponsor');
        } elseif (!empty($goods_info)) {
            $this->data['sponsor'] = isset($goods_info['sponsor'])?$goods_info['sponsor']:null;
        } else {
            $this->data['sponsor'] = false;
        }

        if ($this->input->post('date_start')) {
            $this->data['date_start'] = $this->input->post('date_start');
        } elseif (!empty($goods_info)) {
            $this->data['date_start'] = $goods_info['date_start'];
        } else {
            $this->data['date_start'] = "-";
        }

        if ($this->input->post('date_expire')) {
            $this->data['date_expire'] = $this->input->post('date_expire');
        } elseif (!empty($goods_info)) {
            $this->data['date_expire'] = $goods_info['date_expire'];
        } else {
            $this->data['date_expire'] = "-";
        }

        if (isset($goods_id)) {
            $this->data['goods_id'] = $goods_id;
        } else {
            $this->data['goods_id'] = null;
        }

        if($this->User_model->getClientId()){
            if($this->data['sponsor']){
                redirect('/goods', 'refresh');
            }
        }

        $this->load->model('Client_model');
        $this->data['to_clients'] = $this->Client_model->getClients(array());
        $this->data['client_id'] = $this->User_model->getClientId();
        $site_id = $this->User_model->getSiteId();
        $this->data['site_id'] = $site_id;

        $setting_group_id = $this->User_model->getAdminGroupID();

        $this->data['badge_list'] = array();
        if ($this->User_model->getUserGroupId() != $setting_group_id) {
            $this->data['badge_list'] = $this->Badge_model->getBadgeBySiteId(array("site_id" => $site_id ));
        }
        if (!empty($goods_info)) {
            $goods_private = $this->Goods_model->getGoodsOfClientPrivate($goods_id);
            if(!$this->checkGoodsIsPublic($goods_private['goods_id'])){
                $this->data['badge_list'] = $this->Badge_model->getBadgeBySiteId(array("site_id" => $goods_private['site_id'] ));
            }
        }

        $this->data['point_list'] = array();
        if ($this->User_model->getUserGroupId() != $setting_group_id) {
            $this->data['point_list'] = $this->Reward_model->getAnotherRewardBySiteId($site_id);
        }
        if (!empty($goods_info)) {
            $goods_private = $this->Goods_model->getGoodsOfClientPrivate($goods_id);
            if(!$this->checkGoodsIsPublic($goods_private['goods_id'])){
                $this->data['point_list'] = $this->Reward_model->getAnotherRewardBySiteId($goods_private['site_id']);
            }
        }

        $this->data['main'] = !$import ? 'goods_form' : 'goods_import';

        $this->load->vars($this->data);
        $this->render_page('template');
    }

    public function getBadgeForGoods(){
        if($this->input->get('client_id')){
            $this->load->model('Badge_model');
            $this->data['badge_list'] = $this->Badge_model->getBadgeByClientId(array("client_id" => $this->input->get('client_id') ));

            $this->load->vars($this->data);
            $this->render_page('goods_badge_list_ajax');
        }else{
            $this->output->set_status_header('404');
        }
    }

    public function getCustomForGoods(){
        if($this->input->get('client_id')){
            $this->load->model('Reward_model');

            $this->data['point_list'] = $this->Reward_model->getAnotherRewardByClientId($this->input->get('client_id'));

            $this->load->vars($this->data);
            $this->render_page('goods_reward_list_ajax');
        }else{
            $this->output->set_status_header('404');
        }
    }

    private function validateModify() {

        if ($this->User_model->hasPermission('modify', 'goods')) {
            return true;
        } else {
            return false;
        }
    }

    private function checkOwnerGoods($goodsId){

        $error = null;

        if($this->User_model->getUserGroupId() != $this->User_model->getAdminGroupID()){

            $goods_data = array('site_id'=>$this->User_model->getSiteId());

            $goods_list = $this->Goods_model->getGoodsBySiteId($goods_data);
            $has = false;

            foreach ($goods_list as $goods) {
                if($goods['_id']."" == $goodsId.""){
                    $has = true;
                }
            }

            if(!$has){
                $error = $this->lang->line('error_permission');
            }
        }

        if (!$error) {
            return true;
        } else {
            return false;
        }
    }

    private function validateAccess(){
        if ($this->User_model->hasPermission('access', 'goods')) {
            return true;
        } else {
            return false;
        }
    }

    public function increase_order($goods_id){

        if($this->User_model->getClientId()){
            $this->Goods_model->increaseOrderByOneClient($goods_id);
        }else{
            $this->Goods_model->increaseOrderByOne($goods_id);
        }

        $json = array('success'=>'Okay increase!');

        $this->output->set_output(json_encode($json));
    }

    public function decrease_order($goods_id){

        if($this->User_model->getClientId()){
            $this->Goods_model->decreaseOrderByOneClient($goods_id);
        }else{
            $this->Goods_model->decreaseOrderByOne($goods_id);
        }

        $json = array('success'=>'Okay decrease!');

        $this->output->set_output(json_encode($json));
    }

    public function checkGoodsIsPublic($goods_id){
        $allGoodsFromClients = $this->Goods_model->checkGoodsIsPublic($goods_id);

        if(isset($allGoodsFromClients[0]['client_id'])){
            $firstGoods = $allGoodsFromClients[0]['client_id'];
            foreach($allGoodsFromClients as $goods){
                if($goods['client_id'] != $firstGoods){
                    return true;
                }
            }
            return false;
        }else{
            return true;
        }
    }

    private function addGoods($handle, $data, $redeem, $list_client_id, $list_site_id) {
        $list = array();

        /* build template */
        $d = new MongoDate(strtotime(date("Y-m-d H:i:s")));
        $template = array(
            'description' => $data['description']|'',
            'quantity' => (isset($data['quantity']) && !empty($data['quantity'])) ? (int)$data['quantity'] : null,
            'per_user' => (isset($data['per_user']) && !empty($data['per_user'])) ? (int)$data['per_user'] : null,
            'image'=> isset($data['image']) ? html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8') : '',
            'status' => (bool)$data['status'],
            'deleted' => false,
            'sponsor' => isset($data['sponsor']) ? $data['sponsor'] : false,
            'sort_order' => (int)$data['sort_order']|1,
            'language_id' => 1,
            'redeem' => $redeem,
            'date_start' => null,
            'date_expire' => null,
            'date_added' => $d,
            'date_modified' => $d,
        );
        if(isset($data['date_start']) && $data['date_start'] && isset($data['date_expire']) && $data['date_expire']){
            $date_start_another = strtotime($data['date_start']);
            $date_expire_another = strtotime($data['date_expire']);
            if($date_start_another < $date_expire_another){
                $template['date_start'] = new MongoDate($date_start_another);
                $template['date_expire'] = new MongoDate($date_expire_another);
            }
        }

        /* loop insert into playbasis_goods */
        while (($line = fgets($handle)) !== false) {
            $each = array_merge($template, array('name' => $line));
            $goods_id = $this->Goods_model->addGoods($each);
            foreach ($list_client_id as $client_id) {
                foreach ($list_site_id as $site_id) {
                    array_push($list, array_merge($each, array(
                        'client_id' => $client_id,
                        'site_id' => $site_id,
                        'goods_id' => $goods_id,
                        'group' => $data['name'],
                    )));
                }
            }
        }

        /* bulk insert into playbasis_goods_to_client */
        return $this->Goods_model->addGoodsToClient_bulk($list);
    }
}
