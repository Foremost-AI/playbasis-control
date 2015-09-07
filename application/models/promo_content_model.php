<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Promo_content_model extends MY_Model
{
    public function countPromoContents($client_id, $site_id)
    {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('client_id', new MongoId($client_id));
        $this->mongo_db->where('site_id', new MongoId($site_id));
        $this->mongo_db->where('status', true);
        $total = $this->mongo_db->count('playbasis_promo_content_to_client');

        return $total;
    }

    public function retrievePromoContents($data)
    {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
            $regex = new MongoRegex("/" . preg_quote(utf8_strtolower($data['filter_name'])) . "/i");
            $this->mongo_db->where('name', $regex);
        }

        $sort_data = array(
            '_id',
            'name',
            'status',
            'sort_order'
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

        $this->mongo_db->where('client_id', $data['client_id']);
        $this->mongo_db->where('site_id', $data['site_id']);
        $this->mongo_db->where('status', true);
        return $this->mongo_db->get("playbasis_promo_content_to_client");
    }

    public function retrievePromoContent($promo_content_id)
    {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('_id', new MongoId($promo_content_id));
        $c = $this->mongo_db->get('playbasis_promo_content_to_client');

        if ($c) {
            return $c[0];
        } else {
            return null;
        }
    }

    public function createPromoContent($data)
    {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $insert_data = array(
            'client_id' => $data['client_id'],
            'site_id' => $data['site_id'],
            'name' => strtolower($data['name']),
            'desc' => $data['desc'],
            'date_start' => new MongoDate(strtotime($data['date_start'])),
            'date_end' => new MongoDate(strtotime($data['date_end'])),
            'image' => $data['image'],
            'status' => true,
            'date_added' => new MongoDate(),
            'date_modified' => new MongoDate()
        );
        $insert = $this->mongo_db->insert('playbasis_promo_content_to_client', $insert_data);

        return $insert;
    }

    public function updatePromoContent($data)
    {
        $this->mongo_db->where('client_id', new MongoID($data['client_id']));
        $this->mongo_db->where('site_id', new MongoID($data['site_id']));
        $this->mongo_db->where('_id', new MongoID($data['_id']));

        $this->mongo_db->set('name', $data['name']);
        $this->mongo_db->set('desc', $data['desc']);
        $this->mongo_db->set('image', $data['image']);
        $this->mongo_db->set('date_start', new MongoDate(strtotime($data['date_start'])));
        $this->mongo_db->set('date_end', new MongoDate(strtotime($data['date_end'])));
        $this->mongo_db->set('date_modified', new MongoDate());

        $update = $this->mongo_db->update('playbasis_promo_content_to_client');

        return $update;
    }

    public function deletePromoContent($promo_content_id)
    {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('_id', new MongoID($promo_content_id));
        $this->mongo_db->set('status', false);
        return $this->mongo_db->update('playbasis_promo_content_to_client');
    }
}