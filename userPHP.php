<?php
  $user_id = 0;
  $role_id = NULL;
  $username = "";
  $email = "";
  $password = "";
  $passwordConf = "";
  $profile_picture = "";
  $isEditing = false;
  $users = array();
  $errors = array();

  function getAllRoles(){
    global $conn;
    $sql = "SELECT id, name FROM roles";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $roles = $result->fetch_all(MYSQLI_ASSOC);
    return $roles;
  }
if (isset($_POST['update_user'])) { 
    $user_id = $_POST['user_id'];
    updateUser($user_id);
}

if (isset($_POST['save_user'])) {  
    saveUser();
}

if (isset($_GET["edit_user"])) {
  $user_id = $_GET["edit_user"];
  editUser($user_id);
}

if (isset($_GET['delete_user'])) {
  $user_id = $_GET['delete_user'];
  deleteUser($user_id);
}

function updateUser($user_id) {
  global $conn, $errors, $username, $role_id, $email, $isEditing;
  $errors = validateUser($_POST, ['update_user', 'update_profile']);

  
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  if (count($errors) === 0) {
    if (isset($_POST['role_id'])) {
      $role_id = $_POST['role_id'];
    }
    $sql = "UPDATE users SET username=?, role_id=?, email=?, password=?, profile_picture=? WHERE id=?";
    $result = modifyRecord($sql, 'sisssi', [$username, $role_id, $email, $password, $user_id]);

    if ($result) {
      $_SESSION['success_msg'] = "User account successfully updated";
      exit(0);
    }
  } else {
    $isEditing = true;
  }
}
function saveUser(){
  global $conn, $errors, $username, $role_id, $email, $isEditing;
  $errors = validateUser($_POST, ['save_user']);
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  if (count($errors) === 0) {
    if (isset($_POST['role_id'])) {
      $role_id = $_POST['role_id'];
    }
    $sql = "INSERT INTO users SET username=?, role_id=?, email=?, password=?, profile_picture=?";
    $result = modifyRecord($sql, 'sisss', [$username, $role_id, $email, $password]);

    if($result){
      $_SESSION['success_msg'] = "User account created successfully";
      exit(0);
    } else {
      $_SESSION['error_msg'] = "Something went wrong. Could not save user in Database";
    }
  }
}
function getAdminUsers(){
  global $conn;
  $sql = "SELECT r.name as role, u.id, u.role_id, u.username
          FROM users u
          LEFT JOIN roles r ON u.role_id=r.id
          WHERE role_id IS NOT NULL AND u.id != ?";

  $users = getMultipleRecords($sql, 'i', [$_SESSION['user']['id']]);
  return $users;
}

function editUser($user_id){
  global $conn, $user_id, $role_id, $username, $email, $isEditing, $profile_picture;

  $sql = "SELECT * FROM users WHERE id=?";
  $user = getSingleRecord($sql, 'i', [$user_id]);

  $user_id = $user['id'];
  $role_id = $user['role_id'];
  $username = $user['username'];
  $profile_picture = $user['profile_picture'];
  $email = $user['email'];
  $isEditing = true;
}
function deleteUser($user_id) {
  global $conn;
  $sql = "DELETE FROM users WHERE id=?";
  $result = modifyRecord($sql, 'i', [$user_id]);

  if ($result) {
    $_SESSION['success_msg'] = "User trashed!!";
    exit(0);
  }
}