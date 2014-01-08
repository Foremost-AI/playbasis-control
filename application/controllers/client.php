<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/MY_Controller.php';
class Client extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('User_model');
        // if(!$this->User_model->isLogged()){
        //     redirect('/login', 'refresh');
        // }

        $this->load->model('Client_model');

        $lang = get_lang($this->session, $this->config);
        $this->lang->load($lang['name'], $lang['folder']);
        $this->lang->load("client", $lang['folder']);
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

    public function insert() {

        $this->data['meta_description'] = $this->lang->line('meta_description');
        $this->data['title'] = $this->lang->line('title');
        $this->data['heading_title'] = $this->lang->line('heading_title');
        $this->data['text_no_results'] = $this->lang->line('text_no_results');
        $this->data['form'] = 'client/insert';

        $this->form_validation->set_rules('first_name', $this->lang->line('first_name'), 'trim|required|min_length[2]|max_length[255]|xss_clean|check_space');
        $this->form_validation->set_rules('email', $this->lang->line('email'), 'trim|required|valid_email');

        if (($_SERVER['REQUEST_METHOD'] === 'POST')) {

            $this->data['message'] = null;

            if (!$this->validateModify()) {
                $this->data['message'] = $this->lang->line('error_permission');
            }

            if($this->form_validation->run() && $this->data['message'] == null){
                $clent_id = $this->Client_model->addClient($this->input->post());

                $this->session->set_flashdata('success', $this->lang->line('text_success'));

                redirect('/client/update/'.$clent_id, 'refresh');
            }
        }

        $this->getForm();
    }

    public function update($client_id) {
        $this->data['meta_description'] = $this->lang->line('meta_description');
        $this->data['title'] = $this->lang->line('title');
        $this->data['heading_title'] = $this->lang->line('heading_title');
        $this->data['text_no_results'] = $this->lang->line('text_no_results');
        $this->data['form'] = 'client/update/'.$client_id;

        $this->form_validation->set_rules('first_name', $this->lang->line('entry_firstname'), 'trim|required|min_length[2]|max_length[255]|xss_clean|check_space');
        $this->form_validation->set_rules('last_name', $this->lang->line('entry_lastname'), 'trim|required|min_length[2]|max_length[255]|xss_clean|check_space');
        $this->form_validation->set_rules('company', $this->lang->line('entry_company_name'), 'trim|required|min_length[1]|max_length[255]|xss_clean|check_space');
        $this->form_validation->set_rules('email', $this->lang->line('email'), 'trim|required|valid_email');

        if (($_SERVER['REQUEST_METHOD'] === 'POST') && $this->checkOwnerClient($client_id)) {

            $this->data['message'] = null;

            if (!$this->validateModify()) {
                
                $this->data['message'] = $this->lang->line('error_permission');
            }

            if($this->form_validation->run() && $this->data['message'] == null){

                $this->Client_model->editClient($client_id, $this->input->post());

                if($this->input->post('status')==false){
                    $data = array('client_id'=>$client_id);
                    $results = $this->User_model->getUserByClientId($data);
                    foreach($results as $result){
                        $user_id = $result['user_id'];
                        $this->User_model->disableUser($user_id);
                    }    
                }
                $this->session->set_flashdata('success', $this->lang->line('text_success_update'));

                redirect('/client', 'refresh');
            }
        }

        $this->getForm($client_id);
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
            foreach ($this->input->post('selected') as $client_id) {
                if($this->checkOwnerClient($client_id)){

                    $this->Client_model->deleteClient($client_id);
                    $this->Client_model->deleteClientPersmission($client_id);

                    $data = array('client_id'=>$client_id);
                    $results = $this->User_model->getUserByClientId($data);
                    foreach($results as $result){
                        $user_id = $result['user_id'];
                        $this->User_model->deleteUser($user_id);
                    }
                }
            }

            $this->session->set_flashdata('success', $this->lang->line('text_success_delete'));

            redirect('/client', 'refresh');
        }

        $this->getList(0, site_url('client'));
    }

    public function getList($offset) {

        $offset = $this->input->get('per_page') ? $this->input->get('per_page') : $offset;

        $per_page = 10;

        $this->load->model('Domain_model');
        $this->load->model('Image_model');

        $this->load->library('pagination');

        $this->load->model('Permission_model');

        $parameter_url = "?t=".rand();

        $client_id = $this->User_model->getClientId();
        $site_id = $this->User_model->getSiteId();
        $setting_group_id = $this->User_model->getAdminGroupID();

        if ($this->input->get('filter_name')) {
            $filter_name = $this->input->get('filter_name');
            $parameter_url .= "&filter_name=".$filter_name;
        } else {
            $filter_name = null;
        }

        if ($this->input->get('sort')) {
            $sort = $this->input->get('sort');
            $parameter_url .= "&sort=".$sort;
        } else {
            $sort = 'domain_name';
        }

        if ($this->input->get('order')) {
            $order = $this->input->get('order');
            $parameter_url .= "&order=".$order;
        } else {
            $order = 'ASC';
        }

        $limit = isset($params['limit']) ? $params['limit'] : $per_page ;

        $data = array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'filter_name' => $filter_name,
            'sort'  => $sort,
            'order' => $order,
            'start' => $offset,
            'limit' => $limit
        );

        $total = $this->Client_model->getTotalClients($data);

        $results_client = $this->Client_model->getClients($data);

        if ($results_client) {
            foreach ($results_client as $result) {

                $data_client = array("client_id" => $result['_id']);
                $domain_total = $this->Domain_model->getTotalDomainsByClientId($data_client);

                if ($result['image'] && (S3_IMAGE . $result['image'] != 'HTTP/1.1 404 Not Found' && S3_IMAGE . $result['image'] != 'HTTP/1.0 403 Forbidden')) {
                    $image = $this->Image_model->resize($result['image'], 140, 140);
                }
                else {
                    $image = $this->Image_model->resize('no_image.jpg', 140, 140);
                }

                $this->data['clients'][] = array(
                    'client_id' => $result['_id'],
                    'company'=> $result['company'],
                    // 'first_name' => $result['first_name'], Remove this because we want to show only the company name..
                    // 'last_name' => $result['last_name'], Remove this because we want to show only the company name..
                    'image' => $image,
                    'quantity' => $domain_total,
                    'status' => $result['status'],
                    'selected'    => is_array($this->input->post('selected')) && in_array($result['client_id'], $this->input->post('selected')),
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

        $config['base_url'] = site_url('client/page').$parameter_url;
        $config['total_rows'] = $total;
        $config['per_page'] = $per_page;
        $config["uri_segment"] = 3;
        $choice = $config["total_rows"] / $config["per_page"];
        $config['num_links'] = round($choice);
        $config['page_query_string'] = true;

        $this->pagination->initialize($config);

        $this->data['pagination_links'] = $this->pagination->create_links();

        $this->data['user_group_id'] = $this->User_model->getUserGroupId();
        $this->data['main'] = 'client';
        $this->data['setting_group_id'] = $setting_group_id;

        $this->load->vars($this->data);
        $this->render_page('template');
    }

    private function getForm($client_id=null) {

        $this->load->model('Image_model');
        $this->load->model('Plan_model');

        if (isset($client_id) && ($client_id != 0)) {
            $client_info = $this->Client_model->getClient($client_id);
            $this->data['list_client_id'] = $client_id;
        }else {
            $this->data['list_client_id'] = null;
        }

        if ($this->input->post('company')) {
            $this->data['company'] = $this->input->post('company');
        } elseif (isset($client_id) && ($client_id != 0)) {
            $this->data['company'] = $client_info['company'];
        } else {
            $this->data['company'] = '';
        }

        if ($this->input->post('first_name')) {
            $this->data['first_name'] = $this->input->post('first_name');
        } elseif (isset($client_id) && ($client_id != 0)) {
            $this->data['first_name'] = $client_info['first_name'];
        } else {
            $this->data['first_name'] = '';
        }

        if ($this->input->post('last_name')) {
            $this->data['last_name'] = $this->input->post('last_name');
        } elseif (isset($client_id) && ($client_id != 0)) {
            $this->data['last_name'] = $client_info['last_name'];
        } else {
            $this->data['last_name'] = '';
        }

        if ($this->input->post('mobile')) {
            $this->data['mobile'] = $this->input->post('mobile');
        } elseif (isset($client_id) && ($client_id != 0)) {
            $this->data['mobile'] = $client_info['mobile'];
        } else {
            $this->data['mobile'] = '';
        }

        if ($this->input->post('email')) {
            $this->data['email'] = $this->input->post('email');
        } elseif (isset($client_id) && ($client_id != 0)) {
            $this->data['email'] = $client_info['email'];
        } else {
            $this->data['email'] = '';
        }

        if ($this->input->post('company')) {
            $this->data['company'] = $this->input->post('company');
        } elseif (isset($client_id) && ($client_id != 0)) {
            $this->data['company'] = $client_info['company'];
        } else {
            $this->data['company'] = '';
        }

        if ($this->input->post('image')) {
            $this->data['image'] = $this->input->post('image');
        } elseif (!empty($client_info)) {
            $this->data['image'] = $client_info['image'];
        } else {
            $this->data['image'] = $this->Image_model->resize('no_image.jpg', 100, 100);
        }

        if ($this->input->post('image') && (S3_IMAGE . $this->input->post('image') != 'HTTP/1.1 404 Not Found' && S3_IMAGE . $this->input->post('image') != 'HTTP/1.0 403 Forbidden')) {
            $this->data['thumb'] = $this->Image_model->resize($this->input->post('image'), 100, 100);
        } elseif (!empty($client_info) && $client_info['image'] && (S3_IMAGE . $client_info['image'] != 'HTTP/1.1 404 Not Found' && S3_IMAGE . $client_info['image'] != 'HTTP/1.0 403 Forbidden')) {
            $this->data['thumb'] = $this->Image_model->resize($client_info['image'], 100, 100);
        } else {
            $this->data['thumb'] = $this->Image_model->resize('no_image.jpg', 100, 100);
        }

        $this->data['no_image'] = $this->Image_model->resize('no_image.jpg', 100, 100);

        if ($this->input->post('status')) {
            $this->data['status'] = $this->input->post('status');
        } elseif (!empty($client_info)) {
            $this->data['status'] = $client_info['status'];
        } else {
            $this->data['status'] = 1;
        }

        $this->data['client_id'] = $this->User_model->getClientId();
        $this->data['site_id'] = $this->User_model->getSiteId();

        $this->data['plan_data'] = $this->Plan_model->getPlans();
        $this->data['groups'] = $this->User_model->getUserGroups();

        $this->data['main'] = 'client_form';

        $this->load->vars($this->data);
        $this->render_page('template');
    }

    private function validateModify() {

        if ($this->User_model->hasPermission('modify', 'clients')) {
            return true;
        } else {
            return false;
        }
    }

    private function validateAccess(){
        if ($this->User_model->hasPermission('access', 'clients')) {
            return true;
        } else {
            return false;
        }
    }

    private function checkOwnerClient($clientId){

        $this->load->model('Domain_model');

        $error = null;

        if($this->User_model->getUserGroupId() != $this->User_model->getAdminGroupID()){

            $clients = $this->Domain_model->getDomainsByClientId($this->User_model->getClientId());

            $has = false;

            foreach ($clients as $client) {
                if($client['_id']."" == $clientId.""){
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

    public function autocomplete(){
        $json = array();

        if ($this->input->get('filter_name')) {

            $client_id = $this->User_model->getClientId();
            $site_id = $this->User_model->getSiteId();

            if ($this->input->get('filter_name')) {
                $filter_name = $this->input->get('filter_name');
            } else {
                $filter_name = null;
            }

            $data = array(
                'client_id' => $client_id,
                'site_id' => $site_id,
                'filter_name' => $filter_name
            );

            $results_client = $this->Client_model->getClients($data);

            foreach ($results_client as $result) {
                $json[] = array(
                    'company' => html_entity_decode($result['company'], ENT_QUOTES, 'UTF-8'),
                );
            }

        }

        $this->output->set_output(json_encode($json));
    }

    public function domain($offset=0) {

        // $offset = $this->input->get('per_page') ? $this->input->get('per_page') : $offset;

        // $per_page = 10;

        $this->load->model('Domain_model');
        $this->load->model('Permission_model');
        $this->load->model('Plan_model');

        // $this->load->library('pagination');

        $this->data['domains_data'] = array();

        $data = array(
            'client_id' => $this->input->get('client_id'),
            // 'start' => $offset,
            // 'limit' => $per_page
        );

        $parameter_url = "?t=".rand()."&client_id=".$data['client_id'];

        $total = $this->Domain_model->getTotalDomainsByClientId($data);

        $results = $this->Domain_model->getDomainsByClientId($data);

        if ($results) {
            foreach ($results as $result) {

                $plan_id = $this->Permission_model->getPermissionBySiteId($result['_id']);

                $this->data['domains_data'][] = array(
                    'site_id' => $result['_id'],
                    'client_id' => $result['client_id'],
                    'plan_id' => $plan_id,
                    'domain_name' => $result['domain_name'],
                    'site_name' => $result['site_name'],
                    'keys' => $result['api_key'],
                    'secret' => $result['api_secret'],
                    'date_start' => $result['date_start'],
                    'date_expire' => $result['date_expire'],
                    'limit_users' => $result['limit_users'],
                    'status' => $result['status'],
                    'date_added' => $result['date_added'],
                    'date_modified' => $result['date_modified']
                );
            }
        }

        $data = array(
            'sort' => 'sort_order',
            'order' => 'ASC',
        );

        $this->data['plan_data'] = $this->Plan_model->getPlans($data);

        // $config['base_url'] = site_url('client/domain').$parameter_url;
        // $config['total_rows'] = $total;
        // $config['per_page'] = $per_page;
        // $config["uri_segment"] = 3;
        // $choice = $config["total_rows"] / $config["per_page"];
        // $config['num_links'] = round($choice);
        // $config['page_query_string'] = true;

        // $this->pagination->initialize($config);

        // $this->data['pagination_links'] = $this->pagination->create_links();

        $this->load->vars($this->data);
//        $this->load->view('client_domain');
        $this->render_page('client_domain');
    }

    public function users($offset=0) {

        // $offset = $this->input->get('per_page') ? $this->input->get('per_page') : $offset;

        // $per_page = 10;

        $this->load->model('Domain_model');
        $this->load->model('Permission_model');
        $this->load->model('Plan_model');

        // $this->load->library('pagination');

        $this->data['users'] = array();

        $data = array(
            'client_id' => $this->input->get('client_id'),
            // 'start' => $offset,
            // 'limit' => $per_page
        );

        $parameter_url = "?t=".rand()."&client_id=".$data['client_id'];

        $total = $this->User_model->getTotalUserByClientId($data);

        $results = $this->User_model->getUserByClientId($data);

        if ($results) {
            foreach ($results as $result) {

                $user_data = $this->User_model->getUserInfo($result['user_id']);
                if($user_data) {
                    $this->data['users'][] = array(
                        'user_id' => $result['user_id'],
                        'user_group_id' => $user_data['user_group_id'],
                        'client_id' => $result['client_id'],
                        'first_name' => $user_data['firstname'],
                        'last_name' => $user_data['lastname'],
                        'username' => $user_data['username'],
                        'status' => $user_data['status'],
                        'date_added' => $user_data['date_added'],
                    );
                }
            }
        }
        $this->data['list_client_id'] = $data['client_id'];
        $this->data['groups'] = $this->User_model->getUserGroups();

        // $config['base_url'] = site_url('client/users').$parameter_url;
        // $config['total_rows'] = $total;
        // $config['per_page'] = $per_page;
        // $config["uri_segment"] = 3;
        // $choice = $config["total_rows"] / $config["per_page"];
        // $config['num_links'] = round($choice);
        // $config['page_query_string'] = true;

        // $this->pagination->initialize($config);

        // $this->data['pagination_links'] = $this->pagination->create_links();

        $this->load->vars($this->data);
        $this->render_page('client_user');
    }
}
?>