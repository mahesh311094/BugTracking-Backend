<?php defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class BugTracking extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('api/v1/BugTrackingModel');
        $this->load->model('Common_model');
    }

    // Sign in
    public function signin_post()
    {
        $login_email = $this->post('login_email');
        $password = $this->post('password');
        $token = $this->post('token');

        if (!empty($login_email) && !empty($password)) {
            $login = array(
                'login_email' => $login_email,
                'password' => $password
            );

            $check_user = $this->Common_model->get_data_by_id(USER_TABLE, $login);
            if (!empty($check_user)) {
                $user_id = $check_user['id'];
                $this->Common_model->change_status(USER_TABLE, 'token', $token, 'id', $user_id);

                $message = array(
                    'status' => 'success',
                    'msg' => 'Login Successfully !!',
                    'data' => $check_user
                );
            } else {
                $result = $this->Common_model->get_data_by_id(USER_TABLE, 'login_email', $login_email);
                if (!empty($result)) {
                    $message = array(
                        'status' => 'error',
                        'msg' => 'Wrong Password!'
                    );
                } else {
                    $message = array(
                        'status' => 'error',
                        'msg' => 'Login Email is not registered'
                    );
                }
            }
        } else {
            $message = array(
                'status' => 'error',
                'msg' => 'login_email, password required'
            );
        }
        $this->set_response($message, REST_Controller::HTTP_OK);
    }

    // Add Update Issue
    public function add_issue_post()
    {
        $bug_id = $this->post('bug_id');
        $for_apk = $this->post('for_apk');
        $issue_is = $this->post('issue_is');
        $issue_side = $this->post('issue_side');
        $issue_raised_by = $this->post('issue_raised_by');
        $addressed_to = $this->post('addressed_to');
        $in_which_apk = $this->post('in_which_apk');
        $issue_explain = $this->post('issue_explain');
        $where_problem = $this->post('where_problem');
        $solu_modi = $this->post('solu_modi');
        $other = $this->post('other');
        $priority = $this->post('priority');

        $sender_name = "";

        if (!empty($for_apk) && !empty($issue_is) && !empty($issue_side) && !empty($issue_raised_by) && !empty($addressed_to) && !empty($in_which_apk) && !empty($issue_explain) && !empty($priority)) {
            $data = array(
                'for_apk' => $for_apk,
                'issue_is' => $issue_is,
                'issue_side' => $issue_side,
                'issue_raised_by' => $issue_raised_by,
                'addressed_to' => $addressed_to,
                'in_which_apk' => $in_which_apk,
                'issue_explain' => $issue_explain,
                'where_problem' => $where_problem,
                'solu_modi' => $solu_modi,
                'other' => $other,
                'priority' => $priority
            );

            if (isset($_FILES['screenshot_one'])) {
                $file_name = $_FILES['screenshot_one']['name'];
                $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                if ($ext != "") {
                    $name = time() . '_screenshot_1' . "." . $ext;
                    $tmp_name = $_FILES['screenshot_one']['tmp_name'];
                    $savepath = "uploads/bugs_images/" . $name;
                    move_uploaded_file($tmp_name, $savepath);
                    $data['screenshot_one'] = $name;
                }
            }

            if (isset($_FILES['screenshot_two'])) {
                $file_name = $_FILES['screenshot_two']['name'];
                $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                if ($ext != "") {
                    $name = time() . '_screenshot_2' . "." . $ext;
                    $tmp_name = $_FILES['screenshot_two']['tmp_name'];
                    $savepath = "uploads/bugs_images/" . $name;
                    move_uploaded_file($tmp_name, $savepath);
                    $data['screenshot_two'] = $name;
                }
            }

            if (isset($_FILES['screenshot_three'])) {
                $file_name = $_FILES['screenshot_three']['name'];
                $ext = pathinfo($file_name, PATHINFO_EXTENSION);

                if ($ext != "") {
                    $name = time() . '_screenshot_3' . "." . $ext;
                    $tmp_name = $_FILES['screenshot_three']['tmp_name'];
                    $savepath = "uploads/bugs_images/" . $name;
                    move_uploaded_file($tmp_name, $savepath);
                    $data['screenshot_three'] = $name;
                }
            }

            if (isset($_FILES['audio_clip'])) {
                $file_name = $_FILES['audio_clip']['name'];
                $ext = pathinfo($file_name, PATHINFO_EXTENSION);

                if ($ext != "") {
                    $name = time() . '_audio' . "." . $ext;
                    $tmp_name = $_FILES['audio_clip']['tmp_name'];
                    $savepath = "uploads/bugs_audio/" . $name;
                    move_uploaded_file($tmp_name, $savepath);
                    $data['audio_clip'] = $name;
                }
            }

            $senderData = $this->BugTrackingModel->get_details(USER_TABLE, $issue_raised_by);
            if (!empty($senderData)) {
                $sender_name = $senderData['name'];
            }

            if ($bug_id != "") {
                $result = $this->Common_model->update(ISSUE_TABLE, $data, 'id', $bug_id);

                if (!empty($result)) {
                    $notification_type = "update_issue";
                    $msg =  $sender_name . ' updated issue MHClinic_Bug_' . $bug_id;

                    $this->Common_model->send_topic_notification($bug_id, $issue_raised_by, "BugsTracker", $msg, $notification_type);

                    $message = array(
                        'status' => 'success',
                        'msg' => 'Issue updated successfully',
                    );
                } else {
                    $message = array(
                        'status' => 'error',
                        'msg' => 'Sorry, issue not updated, Try again later !!'
                    );
                }
            } else {
                $result = $this->Common_model->insert_data($data, ISSUE_TABLE);

                if (!empty($result)) {
                    $issue_id = $for_apk . '_Bug_' . $result;
                    $issue_id = $this->Common_model->clean($issue_id);
                    $this->Common_model->change_status(ISSUE_TABLE, 'issue_id', $issue_id, 'id', $result);

                    $notification_type = "add_issue";
                    $msg =  $sender_name . ' added new issue';

                    $this->Common_model->send_topic_notification($result, $issue_raised_by, "BugsTracker", $msg, $notification_type);

                    $message = array(
                        'status' => 'success',
                        'msg' => 'Issue added successfully',
                    );
                } else {
                    $message = array(
                        'status' => 'error',
                        'msg' => 'Sorry, issue not inserted, Try again later !!'
                    );
                }
            }
        } else {
            $message = array(
                'status' => 'error',
                'msg' => 'for_apk, issue_is, issue_side, issue_raised_by, addressed_to, in_which_apk, issue_explain, priority are required'
            );
        }
        $this->set_response($message, REST_Controller::HTTP_OK);
    }

    // Get Issue
    public function get_issue_post()
    {
        $project = $this->post('project');
        $user_id = $this->post('user_id');
        $filter = $this->post('filter');
        $page_no = $this->post('page_no');
        $limit = DEFAULT_PAGE_LIMIT;

        if (!empty($project) && !empty($filter)) {
            if ($filter == 'Pending') {
                $status = "0";
            } else if ($filter == 'Solved') {
                $status = "1";
            } else if ($filter == 'Reject') {
                $status = "2";
            } else {
                $status = "";
            }

            $getDetails = $this->BugTrackingModel->get_issues(ISSUE_TABLE, USER_TABLE, $user_id, $project, $status, $page_no, $limit);

            if (!empty($getDetails)) {
                $message = array(
                    'status' => 'success',
                    'msg' => 'Data Found',
                    'data' => $getDetails
                );
            } else {
                $message = array(
                    'status' => 'error',
                    'msg' => 'Data Not Found',
                    'data' => []
                );
            }
        } else {
            $message = array(
                'status' => 'error',
                'msg' => 'project and filter required'
            );
        }

        $this->set_response($message, REST_Controller::HTTP_OK);
    }

    // Delete Issue
    public function delete_issue_post()
    {
        $id = $this->post('id');

        if (!empty($id)) {
            $result = $this->Common_model->delete(ISSUE_TABLE, 'id', $id);

            if (!empty($result)) {
                $message = array(
                    'status' => 'success',
                    'msg' => 'Deleted Successfully'
                );
            } else {
                $message = array(
                    'status' => 'error',
                    'msg' => 'Something Went Wrong!!'
                );
            }
        } else {
            $message = array(
                'status' => 'error',
                'msg' => 'id required'
            );
        }
        $this->set_response($message, REST_Controller::HTTP_OK);
    }

    // Get Particular Issue
    public function get_issue_details_post()
    {
        $id = $this->post('id');

        if (!empty($id)) {
            $getData = $this->BugTrackingModel->get_issue_details(ISSUE_TABLE, USER_TABLE, $id);
            if (!empty($getData)) {
                $message = array(
                    'status' => 'success',
                    'msg' => 'Data Found',
                    'data' => $getData
                );
            } else {
                $message = array(
                    'status' => 'error',
                    'msg' => 'Data Not Found',
                    'data' => null
                );
            }
        } else {
            $message = array(
                'status' => 'error',
                'msg' => 'id required'
            );
        }
        $this->set_response($message, REST_Controller::HTTP_OK);
    }

    // Update Issue Status
    public function update_issue_status_post()
    {
        $issue_id = $this->post('issue_id');
        $status = $this->post('status');
        $reject_reason = $this->post('reject_reason');

        if (!empty($issue_id) && !empty($status)) {
            if ($status == "Solved") {
                $update_status = "1";
            } else if ($status == "Rejected") {
                $update_status = "2";
            } else {
                $update_status = "";
            }

            $data = array(
                'status' => $update_status,
                'reject_reason' => $reject_reason,
                'issue_updated_on' => date('d-m-Y H:i')
            );

            $updateStatus = $this->Common_model->update(ISSUE_TABLE, $data, 'id', $issue_id);

            if (!empty($updateStatus)) {
                $notification_type = "issue_status";

                $getDetails = $this->Common_model->get_data_by_id(ISSUE_TABLE, 'id', $issue_id);
                if (!empty($getDetails)) {
                    $issue_raised_by = $getDetails['issue_raised_by'];
                    $addressed_to = $getDetails['addressed_to'];
                } else {
                    $issue_raised_by = "";
                    $addressed_to = "";
                }

                $getData = $this->BugTrackingModel->get_details(USER_TABLE, $issue_raised_by);
                if (!empty($getData)) {
                    $token = $getData['token'];
                } else {
                    $token = "";
                }

                $msg =  $addressed_to . ' ' . $status . 'your issue';

                $this->Common_model->send_notification($issue_id, $issue_raised_by, $addressed_to, $token, $msg, $notification_type, NOTIFICATION_TABLE);
                $message = array(
                    'status' => 'success',
                    'msg' => 'Issue ' . $status
                );
            } else {
                $message = array(
                    'status' => 'error',
                    'msg' => 'Something Went Wrong!!'
                );
            }
        } else {
            $message = array(
                'status' => 'error',
                'msg' => 'issue_id and status required'
            );
        }

        $this->set_response($message, REST_Controller::HTTP_OK);
    }

    // Add Comment on Issue
    public function add_comment_post()
    {
        $issue_id = $this->post('issue_id');
        $user_id = $this->post('user_id');
        $comment = $this->post('comment');

        if (!empty($issue_id) && !empty($user_id) && !empty($comment)) {
            $data = array(
                'issue_id' => $issue_id,
                'user_id' => $user_id,
                'comment' => $comment
            );

            $result = $this->Common_model->insert_data($data, COMMENT_TABLE);

            if (!empty($result)) {
                $sender_name = "";
                $notification_type = "add_comment";

                $senderData = $this->BugTrackingModel->get_details(USER_TABLE, $user_id);
                if (!empty($senderData)) {
                    $sender_name = $senderData['name'];
                }
                $msg =  $sender_name . ' has added new comment';

                $this->Common_model->send_topic_notification($issue_id, $user_id, "BugsTracker", $msg, $notification_type);

                $message = array(
                    'status' => 'success',
                    'msg' => 'Comment added successfully',
                );
            } else {
                $message = array(
                    'status' => 'error',
                    'msg' => 'Sorry, comment not inserted, try again later !!'
                );
            }
        } else {
            $message = array(
                'status' => 'error',
                'msg' => 'issue_id, user_id, comment are required'
            );
        }
        $this->set_response($message, REST_Controller::HTTP_OK);
    }

    // Get Comments
    public function get_comments_post()
    {
        $issue_id = $this->post('issue_id');
        $page_no = $this->post('page_no');
        $limit = DEFAULT_PAGE_LIMIT;

        if (!empty($issue_id)) {
            $getData = $this->BugTrackingModel->get_comments(COMMENT_TABLE, USER_TABLE, $issue_id, $page_no, $limit);

            if (!empty($getData)) {
                $message = array(
                    'status' => 'success',
                    'msg' => 'Data Found',
                    'data' => $getData
                );
            } else {
                $message = array(
                    'status' => 'error',
                    'msg' => 'Data Not Found',
                    'data' => []
                );
            }
        } else {
            $message = array(
                'status' => 'error',
                'msg' => 'issue_id required'
            );
        }

        $this->set_response($message, REST_Controller::HTTP_OK);
    }

    // Remove Comment
    public function remove_comments_post()
    {
        $comment_id = $this->post('comment_id');

        if (!empty($comment_id)) {
            $delete = $this->Common_model->delete(COMMENT_TABLE, 'id', $comment_id,);

            if (!empty($delete)) {
                $message = array(
                    'status' => 'success',
                    'msg' => 'Comment deleted successfully'
                );
            } else {
                $message = array(
                    'status' => 'error',
                    'msg' => 'Something went wrong !'
                );
            }
        } else {
            $message = array(
                'status' => 'error',
                'msg' => 'issue_id required'
            );
        }

        $this->set_response($message, REST_Controller::HTTP_OK);
    }

    // Change Password
    public function change_password_post()
    {
        $user_id = $this->post('user_id');
        $old_password = $this->post('old_password');
        $new_password = $this->post('new_password');

        $usersTable = "users";

        if (!empty($user_id) && !empty($old_password) && !empty($new_password)) {
            $check = $this->Common_model->get_data_by_id($usersTable, 'id', $user_id);

            if (!empty($check)) {
                $old_pwd = $check['password'];
                
                if ($old_pwd == $old_password) {
                    if ($old_password != $new_password) {
                        $this->Common_model->change_status($usersTable, 'password', $new_password, 'id', $user_id);
                        $message = array(
                            'status' => 'success',
                            'msg' => 'Password changed successfully'
                        );
                    } else {
                        $message = array(
                            'status' => 'error',
                            'msg' => "New Password can't be same as old password"
                        );
                    }
                } else {
                    $message = array(
                        'status' => 'error',
                        'msg' => "Old Password is wrong"
                    );
                }
            } else {
                $message = array(
                    'status' => 'error',
                    'msg' => 'User not registered'
                );
            }
        } else {
            $message = array(
                'status' => 'error',
                'msg' => 'Old Password & New Password are required',
                'error_msg' => 'User ID, Old Password & New Password are required'
            );
        }
        $this->set_response($message, REST_Controller::HTTP_OK);
    }
}
