<?php
class User_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    function insert($data)
    {
        $this->db->insert('user', $data);
        return $this->db->insert_id();
    }

    public function getUser($name)
    {
        $query = $this->db->get_where('user', array('name' => $name));
        return $query->row_array();
    }

    public function checkUser($id)
    {
        return $this->db->get_where('user',$id)->row_array();
    }

    public function changeActive($id){
        $sql = "UPDATE user SET is_active = 1 WHERE email = ?";
        $query = $this->db->query($sql, array($id));
    return $query;
    }

    public function checkEmail($id)
    {
        return $this->db->get_where('user', $id)->row_array();
    }

    //Model for Ajax Get All Data
    public function getAllData(){
        $query = $this->db->get('user');
        //Check if Data Exists
        if($query->num_rows() > 0){
            return $query->result();
        }else{
            return false;
        }
    }

    public function addData(){
        $set = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = substr(str_shuffle($set), 0, 10);

        $field = [
            'name' => htmlspecialchars($this->input->post('username',true)),
            'email' => htmlspecialchars($this->input->post('email',true)),
            'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
            'is_active' => 1,
            'code' => $code
        ];
        $this->db->insert('user',$field);
        if($this->db->affected_rows() > 0){
            return true;
        }else{
            return false;
        }
    }

    public function editData(){
        $id = $this->input->get('id');
        $this->db->where('id',$id);
        $query = $this->db->get('user');

        if($query->num_rows() > 0){
            return $query->row();
        }else{
            return false;
        }
    }

    public function updateData(){
        $id = $this->input->post('detectId');
            $field = [
            'name' => htmlspecialchars($this->input->post('username',true)),
            'email' => htmlspecialchars($this->input->post('email',true)),
            'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT)
        ];
        $this->db->where('id',$id);
        $this->db->update('user', $field);

        if($this->db->affected_rows() > 0 ){
            return true;
        }else{
            return false;
        }
    }

    function deleteData(){
        $id = $this->input->get('id');
        $this->db->where('id',$id);
        $this->db->delete('user');
        if($this->db->affected_rows() > 0){
            return true;
        }else{
            return false;
        }
    }
}
