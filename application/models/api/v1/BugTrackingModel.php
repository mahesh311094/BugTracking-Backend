<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class BugTrackingModel extends CI_Model
{
    // Get Issues
    public function get_issues($bugTrackingTable, $usersTable, $user_id, $project, $status, $offset, $limit)
    {
        if ($project == "All") {
            $where = "(for_apk = 'MHC' OR for_apk = 'CEMR')";
        }else{
            $where = "for_apk = '$project'";
        }

        if ($user_id == "") {
            if ($status == "") {
                $query1 = "SELECT b.id, b.issue_id, b.for_apk, b.issue_is, b.issue_side, b.addressed_to, b.in_which_apk, b.issue_explain, b.priority, b.status, b.created, b.reject_reason, u.name FROM $bugTrackingTable as b LEFT JOIN $usersTable as u ON b.issue_raised_by = u.id WHERE $where ORDER BY id DESC";
            } else {
                $query1 = "SELECT b.id, b.issue_id, b.for_apk, b.issue_is, b.issue_side, b.addressed_to, b.in_which_apk, b.issue_explain, b.priority, b.status, b.created, b.reject_reason, u.name FROM $bugTrackingTable as b LEFT JOIN $usersTable as u ON b.issue_raised_by = u.id WHERE status = $status AND $where ORDER BY id DESC";
            }
        } else {
            if ($status == "") {
                $query1 = "SELECT b.id, b.issue_id, b.for_apk, b.issue_is, b.issue_side, b.addressed_to, b.in_which_apk, b.issue_explain, b.priority, b.status, b.created, b.reject_reason, u.name FROM $bugTrackingTable as b LEFT JOIN $usersTable as u ON b.issue_raised_by = u.id WHERE $where AND issue_raised_by = $user_id ORDER BY id DESC";
            } else {
                $query1 = "SELECT b.id, b.issue_id, b.for_apk, b.issue_is, b.issue_side, b.addressed_to, b.in_which_apk, b.issue_explain, b.priority, b.status, b.created, b.reject_reason, u.name FROM $bugTrackingTable as b LEFT JOIN $usersTable as u ON b.issue_raised_by = u.id WHERE status = $status AND $where AND issue_raised_by = $user_id ORDER BY id DESC";
            }
        }

        if ($offset == '') {
            $query2 = "";
        } else {
            $off = ($offset - 1) * $limit;
            $query2 = " LIMIT $limit OFFSET $off";
        }
        return $this->db->query($query1 . $query2)->result_array();
    }

    // Get Issue Details
    public function get_issue_details($bugTrackingTable, $usersTable, $id)
    {
        $base_img_url = BUGS_IMAGE_PATH;
        $base_audio_url = BUGS_AUDIO_PATH;
        $q0 = "(CASE WHEN b.screenshot_one != '' THEN concat('$base_img_url', b.screenshot_one) ELSE '' END) as screenshot_one";
        $q1 = "(CASE WHEN b.screenshot_two != '' THEN concat('$base_img_url', b.screenshot_two) ELSE '' END) as screenshot_two";
        $q2 = "(CASE WHEN b.screenshot_three != '' THEN concat('$base_img_url', b.screenshot_three) ELSE '' END) as screenshot_three";
        $q3 = "(CASE WHEN b.audio_clip != '' THEN concat('$base_audio_url', b.audio_clip) ELSE '' END) as audio_clip";

        $query = "SELECT b.id, b.issue_id, b.for_apk, b.issue_is, b.issue_side, u.name, b.addressed_to, b.in_which_apk, b.issue_explain, b.where_problem, $q0, $q1, $q2, $q3, b.solu_modi, b.other, b.priority, b.status, b.created, b.reject_reason, b.issue_updated_on FROM $bugTrackingTable as b LEFT JOIN $usersTable as u ON b.issue_raised_by = u.id WHERE b.id = $id";
        return $this->db->query($query)->row_array();
    }

    // Get Comments
    public function get_comments($commentTable, $usersTable, $issue_id, $offset, $limit)
    {
        $query1 = "SELECT c.id, c.comment, c.created, u.id as user_id, u.name FROM $commentTable as c LEFT JOIN $usersTable as u ON c.user_id = u.id WHERE c.issue_id = $issue_id ORDER BY c.id DESC";

        if ($offset == '') {
            $query2 = "";
        } else {
            $off = ($offset - 1) * $limit;
            $query2 = " LIMIT $limit OFFSET $off";
        }
        return $this->db->query($query1 . $query2)->result_array();
    }

    // Get Details for notification
    public function get_details($table, $id)
    {
        $query = "SELECT token, name FROM $table WHERE id = $id";
        return $this->db->query($query)->row_array();
    }
}
