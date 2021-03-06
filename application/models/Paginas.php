<?php
defined('BASEPATH') OR exit('Você não tem permissão para acessar o script diretamente!');

if(!is_null(website_config('timezone'))){
    date_default_timezone_set(website_config('timezone'));
}

class Paginas extends CI_Model{

    public function __construct(){
        parent::__construct();
    }

    public function SalvaPagina(){

        $pages = $this->input->post('pages');
        $userid = $this->session->userdata('userid');

        $this->db->where('id_user', $userid);
        $this->db->update('paginas', array('status'=>0));

        if(!empty($pages)){

            foreach($pages as $page){

                $this->db->where('id_page', $page);
                $this->db->where('id_user', $userid);
                $queryPages = $this->db->get('paginas');

                if($queryPages->num_rows() > 0){

                    $tokenPage = $this->facebook->TokenAcessoPagina($page);

                    $this->db->where('id_user', $userid);
                    $this->db->where('id_page', $page);
                    $this->db->update('paginas', array('status'=>1, 'token'=>$tokenPage));

                }else{

                    $tokenPage = $this->facebook->TokenAcessoPagina($page);

                    $curtidas_pagina = $this->facebook->getLikesPage($page, $tokenPage);

                    $this->db->insert('paginas', array('id_user'=>$userid, 'id_page'=>$page, 'token'=>$tokenPage, 'curtidas'=>$curtidas_pagina, 'crescimento'=>0, 'status'=>1));
                }
            }
        }
    }

    public function TodasPaginas(){

        $userid = $this->session->userdata('userid');
        $pages = array();

        $this->db->where('id_user', $userid);
        $this->db->where('status', 1);
        $queryPages = $this->db->get('paginas');

        if($queryPages->num_rows() > 0){

            foreach($queryPages->result() as $result){

                $pages[] = array('page'=>$this->facebook->NamePage($result->id_page), 'page_id'=>$result->id_page);
            }

        }

        return $pages;
    }

    public function CrescimentoPaginasTotal(){

        $userid = $this->session->userdata('userid');
        $total = 0;

        $this->db->where('id_user', $userid);
        $this->db->where('status', 1);
        $queryPages = $this->db->get('paginas');

        if($queryPages->num_rows() > 0){

            foreach($queryPages->result() as $result){

                $total += $result->crescimento;
            }

            return $total;

        }else{

            return 0;
        }
    }
}
?>