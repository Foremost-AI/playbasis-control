<?php defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/MY_Controller.php';

class Report_registration extends MY_Controller{

	public function __construct(){

		parent::__construct();
		$this->load->model('User_model');
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
		}
		$this->data['meta_description'] = $this->lang->line('meta_description');
        $this->data['title'] = $this->lang->line('title');
        $this->data['heading_title'] = $this->lang->line('heading_title');
        $this->data['text_no_results'] = $this->lang->line('text_no_results');

        $this->getRegisteredPlayers(0, site_url('report_registration/page'));
		
	}

	public function page($offset = 0){
        if(!$this->validateAccess()){
            echo "<script>alert('".$this->lang->line('error_access')."'); history.go(-1);</script>";
        }

        $this->data['meta_description'] = $this->lang->line('meta_description');
        $this->data['title'] = $this->lang->line('title');
        $this->data['heading_title'] = $this->lang->line('heading_title');
        $this->data['text_no_results'] = $this->lang->line('text_no_results');

        $this->getRegisteredPlayers($offset, site_url('report_registration/page'));
    }

    public function registration_filter(){
        if(!$this->validateAccess()){
            echo "<script>alert('".$this->lang->line('error_access')."'); history.go(-1);</script>";
        }

        $this->data['meta_description'] = $this->lang->line('meta_description');
        $this->data['title'] = $this->lang->line('title');
        $this->data['heading_title'] = $this->lang->line('heading_title');
        $this->data['text_no_results'] = $this->lang->line('text_no_results');

        $this->getRegisteredPlayers(0, site_url('report_registration/page'));
    }

	public function getRegisteredPlayers($offset, $url){
		$offset = $this->input->get('per_page') ? $this->input->get('per_page') : $offset;

		$per_page = 10;
		$parameter_url = "?t=".rand();

		$this->load->library('pagination');

		// $this->load->model('Report_goods_model');
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

        // --> NEW INPUT!

        if($this->input->get('filter_site_id')){
            $filter_site_id = $this->input->get('filter_site_id');
            $parameter_url .= "&site_id=".$filter_site_id;
        }else{
            $filter_site_id = $this->User_model->getSiteId();
        }

        // --> END NEW INPUT!

        $limit =($this->input->get('limit')) ? $this->input->get('limit') : $per_page;

        $client_id = $this->User_model->getClientId();
        $site_id = $this->User_model->getSiteId();

        $data = array(
            'client_id'              => $client_id,
            'site_id'                => $site_id,
            'date_start'             => $filter_date_start,
            'date_expire'            => $filter_date_end,
            'username'               => $filter_username,
            'filter_site_id'         => $filter_site_id,
            'start'                  => $offset,
            'limit'                  => $limit
        );

        $report_total = 0;

        $result = array();

        if($client_id){

            $this->load->model('Report_player_model');

            $report_total = $this->Report_player_model->getTotalPlayers($data);

            $results = $this->Report_player_model->getReportPlayers($data);

        }

        $this->data['reports'] = array();

        foreach($results as $result){

        	$goods_name = null;


            if (!empty($result['image'])){
                $thumb = $result['image'];
            }else{
                $thumb = S3_IMAGE."cache/no_image-40x40.jpg";
            }

            $this->data['reports'][] = array(
                'cl_player_id'      => $result['cl_player_id'],
                'username'          => $result['username'],
                'image'             => $thumb,
                'email'             => $result['email'],
                'date_added'        => datetimeMongotoReadable($result['date_added']),
            );
        }

        $this->data['sites_available'] = $this->Report_player_model->getAllSitesFromClient($client_id);


        $config['base_url'] = $url.$parameter_url;

        $config['total_rows'] = $report_total;
        $config['per_page'] = $per_page;
        $config["uri_segment"] = 3;
        $choice = $config["total_rows"] / $config["per_page"];
        $config['num_links'] = round($choice);
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

        $this->pagination->initialize($config);

        $this->data['pagination_links'] = $this->pagination->create_links();

        $this->data['filter_date_start'] = $filter_date_start;
        // --> This will show only the date, not including the time
        $filter_date_end_exploded = explode(" ",$filter_date_end);
        $this->data['filter_date_end'] = $filter_date_end_exploded[0];
        // --> end
        $this->data['filter_username'] = $filter_username;
        $this->data['filter_site_id'] = $filter_site_id;

        $this->data['main'] = 'report_registration';
        $this->load->vars($this->data);
        $this->render_page('template');


	}

	private function validateAccess(){
        if ($this->User_model->hasPermission('access', 'report/action')) {
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

            $date = $this->input->get('date_expire');
            $currentDate = strtotime($date);
            $futureDate = $currentDate+("86399");
            $filter_date_end = date("Y-m-d H:i:s", $futureDate);
        } else {
            $filter_date_end = date("Y-m-d");

            $date = date("Y-m-d");
            $currentDate = strtotime($date);
            $futureDate = $currentDate+("86399");
            $filter_date_end = date("Y-m-d H:i:s", $futureDate);
        }

        if ($this->input->get('username')) {
            $filter_username = $this->input->get('username');
            $parameter_url .= "&username=".$filter_username;
        } else {
            $filter_username = '';
        }

        $client_id = $this->User_model->getClientId();
        $site_id = $this->User_model->getSiteId();

        $data = array(
            'client_id'              => $client_id,
            'site_id'                => $site_id,
            'date_start'             => $filter_date_start,
            'date_expire'            => $filter_date_end,
            'username'               => $filter_username,
        );

        $results = array();

        if($client_id){

            $this->load->model('Report_player_model');

            $results = $this->Report_player_model->getReportPlayers($data);
        }

        $this->data['reports'] = array();

        foreach ($results as $result) {

            $this->data['reports'][] = array(
                'cl_player_id'      => $result['cl_player_id'],
                'username'          => $result['username'],
                'email'             => $result['email'],
                'date_added'        => datetimeMongotoReadable($result['date_added']),
            );
        }
        $results = $this->data['reports'];

        $this->load->helper('export_data');

        $exporter = new ExportDataExcel('browser', "RegisterReport_" . date("YmdHis") . ".xls");

        $exporter->initialize(); // starts streaming data to web browser

        $exporter->addRow(array(
                $this->lang->line('column_player_id'),
                $this->lang->line('column_username'),
                $this->lang->line('column_email'),
                $this->lang->line('column_date_registered')
            )
        );

        foreach($results as $row)
        {
            $exporter->addRow(array(
                    $row['cl_player_id'],
                    $row['username'],
                    $row['email'],
                    $row['date_added']
                )
            );
        }
        $exporter->finalize();
    }

}