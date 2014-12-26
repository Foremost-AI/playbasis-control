<?php defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/MY_Controller.php';

class Report_goods extends MY_Controller{

	public function __construct(){

		parent::__construct();
		$this->load->model('User_model');
        $this->load->model('Goods_model');
		if(!$this->User_model->isLogged()){
			redirect('/login','refresh');
		}

		$lang = get_lang($this->session, $this->config);
		$this->lang->load($lang['name'], $lang['folder']);
		$this->lang->load("report", $lang['folder']);
	}

	public function index(){
		if(!$this->validateAccess()){
			echo "<script>alert('".$this->lang->line('error_access')."'); history.go(-1);</script>";
            die();
		}
		$this->data['meta_description'] = $this->lang->line('meta_description');
        $this->data['title'] = $this->lang->line('title');
        $this->data['heading_title'] = $this->lang->line('heading_title');
        $this->data['text_no_results'] = $this->lang->line('text_no_results');

        $this->getGoodsList(0, site_url('report_goods/page'));
		
	}

	public function page($offset = 0){
        if(!$this->validateAccess()){
            echo "<script>alert('".$this->lang->line('error_access')."'); history.go(-1);</script>";
            die();
        }

        $this->data['meta_description'] = $this->lang->line('meta_description');
        $this->data['title'] = $this->lang->line('title');
        $this->data['heading_title'] = $this->lang->line('heading_title');
        $this->data['text_no_results'] = $this->lang->line('text_no_results');

        $this->getGoodsList($offset, site_url('report_goods/page'));
    }

    public function goods_filter(){
        if(!$this->validateAccess()){
            echo "<script>alert('".$this->lang->line('error_access')."'); history.go(-1);</script>";
            die();
        }

        $this->data['meta_description'] = $this->lang->line('meta_description');
        $this->data['title'] = $this->lang->line('title');
        $this->data['heading_title'] = $this->lang->line('heading_title');
        $this->data['text_no_results'] = $this->lang->line('text_no_results');

        $this->getGoodsList(0, site_url('report_goods/page'));
    }

	public function getGoodsList($offset, $url){
		$offset = $this->input->get('per_page') ? $this->input->get('per_page') : $offset;

		$per_page = NUMBER_OF_RECORDS_PER_PAGE;
		$parameter_url = "?t=".rand();

		$this->load->library('pagination');

		$this->load->model('Report_goods_model');
		$this->load->model('Image_model');
		$this->load->model('Player_model');

		
		if ($this->input->get('date_start')) {
            $filter_date_start = $this->input->get('date_start');
            $parameter_url .= "&date_start=".$filter_date_start;
        } else {
            $filter_date_start = date("Y-m-d", strtotime("-30 days")); ;
        }

        if ($this->input->get('date_expire')) {
            $filter_date_end = $this->input->get('date_expire');
            $parameter_url .= "&date_expire=".$filter_date_end;

            //--> This will enable to search on the day until the time 23:59:59
            $date = $this->input->get('date_expire');
            $currentDate = strtotime($date);
            $futureDate = $currentDate+("86399");
            $filter_date_end = date("Y-m-d H:i:s", $futureDate);
            //--> end
        } else {
            $filter_date_end = date("Y-m-d");

            //--> This will enable to search on the current day until the time 23:59:59
            $date = date("Y-m-d");
            $currentDate = strtotime($date);
            $futureDate = $currentDate+("86399");
            $filter_date_end = date("Y-m-d H:i:s", $futureDate);
            //--> end
        }

        if ($this->input->get('username')) {
            $filter_username = $this->input->get('username');
            $parameter_url .= "&username=".$filter_username;
        } else {
            $filter_username = '';
        }

        $goods = null;
        $is_group = false;
        if ($this->input->get('goods_id')) {
            $filter_goods_id = $this->input->get('goods_id');
            $goods = $this->Goods_model->getGoodsOfClientPrivate($filter_goods_id);
            $is_group = array_key_exists('group', $goods);
            $parameter_url .= "&goods_id=".$filter_goods_id;
        } else {
            $filter_goods_id = '';
        }

        $limit =($this->input->get('limit')) ? $this->input->get('limit') : $per_page;

        $client_id = $this->User_model->getClientId();
        $site_id = $this->User_model->getSiteId();

        $data = array(
            'client_id'              => $client_id,
            'site_id'                => $site_id,
            'date_start'             => $filter_date_start,
            'date_expire'            => $filter_date_end,
            'username'               => $filter_username,
            'goods_id'               => ($is_group ? $goods['group'] : $filter_goods_id),
            'is_group'               => $is_group,
            'start'                  => $offset,
            'limit'                  => $limit
        );

        $report_total = 0;

        $results = array();

        if($client_id){
        	$report_total = $this->Report_goods_model->getTotalReportGoods($data);
        	$results = $this->Report_goods_model->getReportGoods($data);
        }

        $this->data['reports'] = array();

        foreach($results as $result){

        	$goods_name = null;

        	$player = $this->Player_model->getPlayerById($result['pb_player_id'], $data['site_id']);

            if (!empty($player['image'])){
                $thumb = $player['image'];
            }else{
                $thumb = S3_IMAGE."cache/no_image-40x40.jpg";
            }
        	/*if (!empty($player['image']) && $player['image'] && ($player['image'] != 'HTTP/1.1 404 Not Found' && $player['image'] != 'HTTP/1.0 403 Forbidden')) {
                $thumb = $player['image'];
            } else {
                $thumb = $this->Image_model->resize('no_image.jpg', 40, 40);
            }*/

            $this->data['reports'][] = array(
                'cl_player_id'      => $player['cl_player_id'],
                'username'          => $player['username'],
                'image'             => $thumb,
                'email'             => $player['email'],
                'date_added'        => datetimeMongotoReadable($result['date_added']),
                'goods_name'       => $result['goods_name'],
                // 'value'             => $result['value']
                'value'             => $result['amount'],
                // 'redeem'            => $result['redeem']
            );
        }

        $this->data['goods_available'] = array();

        if($client_id){
            $allGoodsAvailable = $this->getList($site_id);
            $all_goods = array();
            foreach($allGoodsAvailable as $goods){
                $is_group = array_key_exists('group', $goods);
                $all_goods[] = array('_id' => $goods['goods_id']->{'$id'}, 'name' => $goods[$is_group ? 'group' : 'name']);
            }
            $this->data['goods_available'] = $all_goods;
        }

        $config['base_url'] = $url.$parameter_url;

        $config['total_rows'] = $report_total;
        $config['per_page'] = $per_page;
        $config["uri_segment"] = 3;

        $config['num_links'] = NUMBER_OF_ADJACENT_PAGES;
        $config['page_query_string'] = true;

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

        $config['first_link'] = 'First';
        $config['first_tag_open'] = '<li class="page_index_nav next">';
        $config['first_tag_close'] = '</li>';

        $config['last_link'] = 'Last';
        $config['last_tag_open'] = '<li class="page_index_nav prev">';
        $config['last_tag_close'] = '</li>';

        $this->pagination->initialize($config);

        $this->data['pagination_links'] = $this->pagination->create_links();
        $this->data['pagination_total_pages'] = ceil(floatval($config["total_rows"]) / $config["per_page"]);
        $this->data['pagination_total_rows'] = $config["total_rows"];

        $this->data['filter_date_start'] = $filter_date_start;
        // --> This will show only the date, not including the time
        $filter_date_end_exploded = explode(" ",$filter_date_end);
        $this->data['filter_date_end'] = $filter_date_end_exploded[0];
        // --> end
        $this->data['filter_username'] = $filter_username;
        $this->data['filter_goods_id'] = $filter_goods_id;

        $this->data['main'] = 'report_goods';
        $this->load->vars($this->data);
        $this->render_page('template');
	}

	private function getList($site_id) {
		$results = $this->Goods_model->getGroupsAggregate($site_id);
		$ids = array();
		$group_name = array();
		foreach ($results as $i => $result) {
			$group = $result['_id']['group'];
			$quantity = $result['quantity'];
			$list = $result['list'];
			$first = array_shift($list); // skip first one
			$group_name[$first->{'$id'}] = array('group' => $group, 'quantity' => $quantity);
			$ids = array_merge($ids, $list);
		}
		return $this->Goods_model->getGoodsBySiteId(array('site_id' => $site_id, 'sort' => 'sort_order', '$nin' => $ids));
	}

	private function validateAccess(){
        if($this->User_model->isAdmin()){
            return true;
        }
        $this->load->model('Feature_model');
        $client_id = $this->User_model->getClientId();

        if ($this->User_model->hasPermission('access', 'report/action') &&  $this->Feature_model->getFeatureExitsByClientId($client_id, 'report/action')) {
            return true;
        } else {
            return false;
        }
    }

    public function actionDownload() {

        $parameter_url = "?t=".rand();
        $this->load->model('Report_goods_model');
        $this->load->model('Image_model');
        $this->load->model('Player_model');


        if ($this->input->get('date_start')) {
            $filter_date_start = $this->input->get('date_start');
            $parameter_url .= "&date_start=".$filter_date_start;
        } else {
            $filter_date_start = date("Y-m-d", strtotime("-30 days")); ;
        }

        if ($this->input->get('date_expire')) {
            $filter_date_end = $this->input->get('date_expire');
            $parameter_url .= "&date_expire=".$filter_date_end;

            //--> This will enable to search on the day until the time 23:59:59
            $date = $this->input->get('date_expire');
            $currentDate = strtotime($date);
            $futureDate = $currentDate+("86399");
            $filter_date_end = date("Y-m-d H:i:s", $futureDate);
            //--> end
        } else {
            $filter_date_end = date("Y-m-d");

            //--> This will enable to search on the current day until the time 23:59:59
            $date = date("Y-m-d");
            $currentDate = strtotime($date);
            $futureDate = $currentDate+("86399");
            $filter_date_end = date("Y-m-d H:i:s", $futureDate);
            //--> end
        }

        if ($this->input->get('username')) {
            $filter_username = $this->input->get('username');
            $parameter_url .= "&username=".$filter_username;
        } else {
            $filter_username = '';
        }

        $goods = null;
        $is_group = false;
        if ($this->input->get('goods_id')) {
            $filter_goods_id = $this->input->get('goods_id');
            $goods = $this->Goods_model->getGoodsOfClientPrivate($filter_goods_id);
            $is_group = array_key_exists('group', $goods);
            $parameter_url .= "&goods_id=".$filter_goods_id;
        } else {
            $filter_goods_id = '';
        }

        $client_id = $this->User_model->getClientId();
        $site_id = $this->User_model->getSiteId();

        $data = array(
            'client_id'              => $client_id,
            'site_id'                => $site_id,
            'date_start'             => $filter_date_start,
            'date_expire'            => $filter_date_end,
            'username'               => $filter_username,
            'goods_id'               => ($is_group ? $goods['group'] : $filter_goods_id),
            'is_group'               => $is_group
        );

        $results = array();

        if($client_id){
            $results = $this->Report_goods_model->getReportGoods($data);
        }

        $this->data['reports'] = array();

        foreach($results as $result){

            $goods_name = null;

            $player = $this->Player_model->getPlayerById($result['pb_player_id'], $data['site_id']);

            if (!empty($player['image'])){
                $thumb = $player['image'];
            }else{
                $thumb = S3_IMAGE."cache/no_image-40x40.jpg";
            }

            $this->data['reports'][] = array(
                'cl_player_id'      => $player['cl_player_id'],
                'username'          => $player['username'],
                'image'             => $thumb,
                'email'             => $player['email'],
                'date_added'        => datetimeMongotoReadable($result['date_added']),
                'goods_name'       => $result['goods_name'],
                // 'value'             => $result['value']
                'amount'             => $result['amount'],
                // 'redeem'            => $result['redeem']
            );
        }

        $results = $this->data['reports'];

        $this->load->helper('export_data');

        $exporter = new ExportDataExcel('browser', "GoodsReport_" . date("YmdHis") . ".xls");

        $exporter->initialize(); // starts streaming data to web browser

        $exporter->addRow(array(
            $this->lang->line('column_player_id'),
            $this->lang->line('column_username'),
            $this->lang->line('column_email'),
            $this->lang->line('column_goods_name'),
            $this->lang->line('column_goods_amount'),
            $this->lang->line('column_date_added')
            )
        );
        
        foreach($results as $row)
        {
            $exporter->addRow(array(
                    $row['cl_player_id'],
                    $row['username'],
                    $row['email'],
                    $row['goods_name'],
                    $row['amount'],
                    $row['date_added']
                )
            );
        }
        $exporter->finalize();

    }
}