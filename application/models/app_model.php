<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class App_model extends MY_Model
{

    public function getApp($app_id) {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('_id', new MongoID($app_id));
        $results = $this->mongo_db->get("playbasis_client_site");

        return $results ? $results[0] : null  ;
    }

    public function getTotalPlatFormsByClientId($data) {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('deleted', false);
        $this->mongo_db->where('client_id', new MongoID($data['client_id']));

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $this->mongo_db->where('status', (bool)$data['filter_status']);
        }

        $client_data = $this->mongo_db->count("playbasis_platform_client_site");

        return $client_data;
    }

    public function getPlatFormsByClientId($data) {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('deleted', false);
        $this->mongo_db->where('client_id', new MongoID($data['client_id']));

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $this->mongo_db->where('status', (bool)$data['filter_status']);
        }

        $sort_data = array(
            'status',
            '_id'
        );

        if (isset($data['order']) && (utf8_strtolower($data['order']) == 'desc')) {
            $order = -1;
        } else {
            $order = 1;
        }

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $this->mongo_db->order_by(array($data['sort'] => $order));
        } else {
            $this->mongo_db->order_by(array('name' => $order));
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $this->mongo_db->limit((int)$data['limit']);
            $this->mongo_db->offset((int)$data['start']);
        }

        $client_data = $this->mongo_db->get("playbasis_platform_client_site");

        return $client_data;
    }

    public function getTotalAppsByClientId($data) {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('deleted', false);
        $this->mongo_db->where('client_id', new MongoID($data['client_id']));

        if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
            $regex = new MongoRegex("/".utf8_strtolower($data['filter_name'])."/i");
            $this->mongo_db->where('domain_name', $regex);
        }

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $this->mongo_db->where('status', (bool)$data['filter_status']);
        }

        $total = $this->mongo_db->count("playbasis_client_site");

        return $total;
    }

    public function getAppsByClientId($data) {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('deleted', false);
        $this->mongo_db->where('client_id', new MongoID($data['client_id']));

        if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
            $regex = new MongoRegex("/".utf8_strtolower($data['filter_name'])."/i");
            $this->mongo_db->where('domain_name', $regex);
        }

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $this->mongo_db->where('status', (bool)$data['filter_status']);
        }

        $sort_data = array(
            'domain_name',
            'status',
            'sort_order',
            '_id'
        );

        if (isset($data['order']) && (utf8_strtolower($data['order']) == 'desc')) {
            $order = -1;
        } else {
            $order = 1;
        }

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $this->mongo_db->order_by(array($data['sort'] => $order));
        } else {
            $this->mongo_db->order_by(array('name' => $order));
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $this->mongo_db->limit((int)$data['limit']);
            $this->mongo_db->offset((int)$data['start']);
        }

        $client_data = $this->mongo_db->get("playbasis_client_site");

        return $client_data;
    }

    public function getAppsBySiteId($site_id) {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('deleted', false);
        $this->mongo_db->where('_id', new MongoID($site_id));

        $results = $this->mongo_db->get("playbasis_client_site");

        return $results ? $results[0] : null;
    }

    public function getTotalApps($data) {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('deleted', false);

        if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
            $regex = new MongoRegex("/".utf8_strtolower($data['filter_name'])."/i");
            $this->mongo_db->where('domain_name', $regex);
        }

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $this->mongo_db->where('status', (bool)$data['filter_status']);
        }

        $total = $this->mongo_db->count("playbasis_client_site");

        return $total;
    }

    public function getApps($data) {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('deleted', false);

        if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
            $regex = new MongoRegex("/".utf8_strtolower($data['filter_name'])."/i");
            $this->mongo_db->where('domain_name', $regex);
        }

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $this->mongo_db->where('status', (bool)$data['filter_status']);
        }

        $sort_data = array(
            'domain_name',
            'status',
            'sort_order',
            '_id'
        );

        if (isset($data['order']) && (utf8_strtolower($data['order']) == 'desc')) {
            $order = -1;
        } else {
            $order = 1;
        }

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $this->mongo_db->order_by(array($data['sort'] => $order));
        } else {
            $this->mongo_db->order_by(array('name' => $order));
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $this->mongo_db->limit((int)$data['limit']);
            $this->mongo_db->offset((int)$data['start']);
        }

        $client_data = $this->mongo_db->get("playbasis_client_site");

        return $client_data;
    }

//    public function resetToken($site_id) {
//        $this->set_site_mongodb($this->session->userdata('site_id'));
//
//        $secret = $this->genAccessSecret($site_id);
//
//        $token = $this->checkSecret($secret);
//
//        if ($token == '0' || $token == 0) {
//            $this->mongo_db->where('_id', new MongoID($site_id));
//            $this->mongo_db->set('api_secret', $secret);
//            $this->mongo_db->update('playbasis_client_site');
//        }
//    }

    public function resetToken($platform_id) {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $secret = $this->genAccessSecret($platform_id);

        $token = $this->checkSecret($secret);

        if ($token == '0' || $token == 0) {
            $this->mongo_db->where('_id', new MongoID($platform_id));
            $this->mongo_db->set('api_secret', $secret);
            $this->mongo_db->update('playbasis_platform_client_site');
        }

        return $secret;
    }

    public function checkSecret($secret) {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('deleted', false);
        $this->mongo_db->where('api_secret', $secret);

        $total = $this->mongo_db->count("playbasis_client_site");

        return $total;
    }

    private function genAccessKey($site_id) {
        $salt = "R0b3rt pl@yb@s1s";

        $key = sprintf("%u",crc32(sha1($site_id.time()).$salt));

        return $key;
    }

    private function genAccessSecret($site_id) {
        $salt = "R0b3rt pl@yb@s1s";

        $secret = md5($site_id.time().$salt);

        return $secret;
    }

    public function deletePlatform($platform_id){
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $platform_info = $this->getPlatform($platform_id);

        $this->mongo_db->where('_id', new MongoID($platform_id));
        $this->mongo_db->set('deleted', true);
        $this->mongo_db->set('status', false);
        $this->mongo_db->update('playbasis_platform_client_site');

        $data_filter = array("site_id" => $platform_info["site_id"]);
        if($this->getTotalPlatFormByAppId($data_filter) < 1){
            $this->mongo_db->where('_id', new MongoID($platform_info["site_id"]));
            $this->mongo_db->set('deleted', true);
            $this->mongo_db->set('status', false);
            $this->mongo_db->update('playbasis_client_site');
        }
    }

    public function deleteApp($site_id){
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('site_id', new MongoID($site_id));
        $this->mongo_db->set('deleted', true);
        $this->mongo_db->set('status', false);
        $this->mongo_db->update_all('playbasis_platform_client_site');

        $this->mongo_db->where('_id', new MongoID($site_id));
        $this->mongo_db->set('deleted', true);
        $this->mongo_db->set('status', false);
        $this->mongo_db->update('playbasis_client_site');
    }

    public function deleteAppByClientId($client_id){
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('client_id', new MongoID($client_id));
        $this->mongo_db->set('deleted', true);
        $this->mongo_db->set('status', false);
        $this->mongo_db->update_all('playbasis_client_site');
    }

    public function checkAppExists($data){
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $domain = preg_replace("/http:\/\//", "", $data['domain_name']);
        $domain = preg_replace("/https:\/\//", "", $domain);

        $this->mongo_db->where('domain_name', strtolower($domain));
        $this->mongo_db->where('deleted', false);

        $c = $this->mongo_db->count('playbasis_client_site');
        return $c > 0;
    }

    public function addApp($data){
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $domain = preg_replace("/http:\/\//", "", $data['app_name']);
        $domain = preg_replace("/https:\/\//", "", $domain);

        $data_insert = array(
            'client_id' =>  new MongoID($data['client_id']),
            'domain_name' => $domain|'',
            'site_name' => $data['app_name']|'' ,
            'api_key'=> '',
            'api_secret' => '',
            'image' => isset($data['image'])? html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8') : '',
            'status' => true,
            'deleted' => false,
            'last_send_limit_users' => null,
            'date_added' => new MongoDate(strtotime(date("Y-m-d H:i:s"))),
            'date_modified' => new MongoDate(strtotime(date("Y-m-d H:i:s"))),
        );

        $data['site_id'] = $this->mongo_db->insert('playbasis_client_site', $data_insert);

        $data_insert = array(
            'client_id' =>  new MongoID($data['client_id']),
            'site_id' =>  new MongoID($data['site_id']),
            'platform' => strtolower($data['platform']),
            'data' => $data['data'],
            'api_key'=> '',
            'api_secret' => '',
            'status' => true,
            'deleted' => false,
            'date_added' => new MongoDate(strtotime(date("Y-m-d H:i:s"))),
            'date_modified' => new MongoDate(strtotime(date("Y-m-d H:i:s"))),
        );

        $c = $this->mongo_db->insert('playbasis_platform_client_site', $data_insert);

        $keys = $this->genAccessKey($c);
        $secret = $this->genAccessSecret($c);

        $this->mongo_db->where('_id',  new MongoID($c));
        $this->mongo_db->set('api_key', $keys);
        $this->mongo_db->set('api_secret', $secret);
        $this->mongo_db->update('playbasis_platform_client_site');

        return $data['site_id'];
    }

    public function editApp($platform_id, $data){
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('_id', new MongoID($platform_id));

        if(isset($data['platform']) && !is_null($data['platform'])){
            $this->mongo_db->set('platform', utf8_strtolower($data['platform']));
        }

        if(isset($data['data']) && !is_null($data['data'])){
            $this->mongo_db->set('data', $data['data']);
        }

        $this->mongo_db->set('date_modified', new MongoDate(strtotime(date("Y-m-d H:i:s"))));

        return $this->mongo_db->update('playbasis_platform_client_site');

    }

    public function addPlatform($app_id, $data){

        $app_info = $this->getApp($app_id);

        $data_insert = array(
            'client_id' =>  new MongoID($app_info['client_id']),
            'site_id' =>  new MongoID($app_info['_id']),
            'platform' => strtolower($data['platform']),
            'data' => $data['data'],
            'api_key'=> '',
            'api_secret' => '',
            'status' => true,
            'deleted' => false,
            'date_added' => new MongoDate(strtotime(date("Y-m-d H:i:s"))),
            'date_modified' => new MongoDate(strtotime(date("Y-m-d H:i:s"))),
        );

        $c = $this->mongo_db->insert('playbasis_platform_client_site', $data_insert);

        $keys = $this->genAccessKey($c);
        $secret = $this->genAccessSecret($c);

        $this->mongo_db->where('_id',  new MongoID($c));
        $this->mongo_db->set('api_key', $keys);
        $this->mongo_db->set('api_secret', $secret);
        $this->mongo_db->update('playbasis_platform_client_site');

        return $c;
    }

    public function getPlatFormByAppId($data){
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('deleted', false);
        $this->mongo_db->where('site_id', new MongoID($data["site_id"]));

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $this->mongo_db->where('status', (bool)$data['filter_status']);
        }

        $sort_data = array(
            'status',
            '_id'
        );

        if (isset($data['order']) && (utf8_strtolower($data['order']) == 'desc')) {
            $order = -1;
        } else {
            $order = 1;
        }

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $this->mongo_db->order_by(array($data['sort'] => $order));
        } else {
            $this->mongo_db->order_by(array('name' => $order));
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $this->mongo_db->limit((int)$data['limit']);
            $this->mongo_db->offset((int)$data['start']);
        }

        $app_data = $this->mongo_db->get("playbasis_platform_client_site");

        return $app_data;
    }

    public function getTotalPlatFormByAppId($data) {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('deleted', false);
        $this->mongo_db->where('site_id', new MongoID($data["site_id"]));

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $this->mongo_db->where('status', (bool)$data['filter_status']);
        }

        $total = $this->mongo_db->count("playbasis_platform_client_site");

        return $total;
    }

    public function getPlatform($platform_id) {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('_id', new MongoID($platform_id));
        $results = $this->mongo_db->get("playbasis_platform_client_site");

        return $results ? $results[0] : null  ;
    }

//    public function moveOldtoNewSystem($data){
//        $this->set_site_mongodb($this->session->userdata('site_id'));
//
//        $data_insert = array(
//            'client_id' =>  new MongoID($data['client_id']),
//            'site_id' =>  new MongoID($data['site_id']),
//            'platform' => strtolower($data['platform']),
//            'api_key'=> $data['api_key'],
//            'api_secret' => $data['api_secret'],
//            'data' => $data['data'],
//            'status' => true,
//            'deleted' => false,
//            'date_added' => new MongoDate(strtotime(date("Y-m-d H:i:s"))),
//            'date_modified' => new MongoDate(strtotime(date("Y-m-d H:i:s"))),
//        );
//
//        return $this->mongo_db->insert('playbasis_platform_client_site', $data_insert);
//    }
}
?>