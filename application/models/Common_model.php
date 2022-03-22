<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class COMMON_MODEL extends CI_Model
{

	public function insert_data($data, $tbl_name)
	{
		$sql = $this->db->insert($tbl_name, $data);
		return ($this->db->insert_id());
	}

	public function update($tbl, $data, $field, $value)
	{
		$this->db->where($field, $value);
		return $this->db->update($tbl, $data);
	}

	public function change_status($table, $column, $value, $uniqueNameCol, $uniqueColValue)
	{
		$query = $this->db->query("UPDATE " . $table . " SET `" . $column . "` = '" . $value . "' WHERE `" . $uniqueNameCol . "` = '" . $uniqueColValue . "' ");
		return $query;
	}

	public function get_rows($tbl, $field = 0, $value = 0)
	{
		if (!empty($field)) {
			$this->db->where($field, $value);
		}
		return $this->db->get($tbl)->num_rows();
	}

	public function get_data_by_id($tbl, $field = 0, $value = 0)
	{
		if (!empty($field)) {
			$this->db->where($field, $value);
		}
		return $this->db->get($tbl)->row_array();
	}

	public function get_data_by_id1($tbl, $field = 0, $value = 0)
	{
		if (!empty($field)) {
			$this->db->where($field, $value);
		}
		return $this->db->get($tbl)->result_array();
	}

	public function delete($tbl, $field = 0, $value = 0)
	{
		$this->db->where($field, $value);
		return $this->db->delete($tbl);
	}

	public function count_data_with_id($tbl, $field = 0, $value = 0)
	{
		if (!empty($field)) {
			$this->db->where($field, $value);
		}
		return $this->db->count_all_results($tbl);
	}

	public function num_data($id, $tbl)
	{
		$this->db->select('*');
		$this->db->order_by($id);
		$result = $this->db->get($tbl);
		return $result->num_rows();
	}

	public function get_desc_order($table, $field = 0, $value = 0)
	{
		$query = "SELECT * FROM $table WHERE $field = '$value' ORDER BY id DESC";
		return $this->db->query($query)->result_array();
	}

	public function get_desc_order_id($table, $field, $value)
	{
		$query = "SELECT * FROM $table WHERE $field = '$value' ORDER BY id DESC LIMIT 1";
		return $this->db->query($query)->row_array();
	}

	// Count Notification
	public function count_notification($table, $receiver_id)
	{
		$sql =  $this->db->query("SELECT count(id) as count from $table where receiver_id = '$receiver_id'");
		return $sql->row_array();
	}

	// Delete Notification
	public function delete_notification($table, $receiver_id, $limit)
	{
		$sql =  $this->db->query("Delete from $table where receiver_id = '$receiver_id' ORDER BY id ASC LIMIT $limit");
		return $sql;
	}

	// Remove Whitespace
    function clean($lbl)
    {
        $name = trim($lbl);
        $lbl = str_replace(' ', '', $name);
        return $lbl;
    }

	// Send Notification Function (Store in database)
    public function send_notification($foreign_id, $sender_id, $receiver_id, $token, $message, $type, $table)
    {
        $GOOGLE_API_KEY = GOOGLE_API_KEY;
        $GOOGLE_FCM_URL = "https://fcm.googleapis.com/fcm/send";

        $data = array(
            "title" => APP_NAME,
            'foreign_id' => $foreign_id,
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'notification' => $message,
            'type' => $type
        );

        $notification = array(
            "title" => APP_NAME,
            "body" => $message
        );

        $fields = array(
            'to' => $token,
            'priority' => "high",
            'notification' => $notification,
            'data' => $data,
        );

        $headers = array(
            'Authorization: key=' . $GOOGLE_API_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $GOOGLE_FCM_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Problem occurred: ' . curl_error($ch));
        }
        curl_close($ch);

        $data100 = array(
            'foreign_id' => $foreign_id,
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            "notification" => $message,
            "type" => $type
        );

        $inst = $this->Common_model->insert_data($data100, $table);

        $notificationCount = $this->Common_model->count_notification($table, $receiver_id);
        if ($notificationCount['count'] > NOTIFICATION_ALLOWED) {
            $lim = $notificationCount['count'] - NOTIFICATION_ALLOWED;
            $this->Common_model->delete_notification($table, $receiver_id, $lim);
        }

        if ($inst) {
            return true;
        } else {
            return false;
        }
    }

	public function send_topic_notification($sender_id, $topics, $message, $type)
    {
        $GOOGLE_API_KEY = GOOGLE_API_KEY;
        $GOOGLE_FCM_URL = "https://fcm.googleapis.com/fcm/send";

        $data = array(
            'title' => APP_NAME,
            'message' => $message,
            'sender_id' => $sender_id,
            'type' => $type,
        );

        $notification = array(
            "title" => APP_NAME,
            "body" => $message,
        );

        $fields = array(
            'to' => '/topics/' . $topics,
            'priority' => "high",
            'notification' => $notification,
            'data' => $data
        );

        $headers = array(
            'Authorization: key=' . $GOOGLE_API_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $GOOGLE_FCM_URL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Problem occurred: ' . curl_error($ch));
        }
        curl_close($ch);

        return $result;
    }
}