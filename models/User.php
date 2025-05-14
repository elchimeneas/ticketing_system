<?php
class User
{
  private $conn;
  private $table_name = "users";

  public $id;
  public $name;
  public $email;
  public $password;
  public $role;
  public $created_at;
  public $profile_pic;

  public function __construct($db)
  {
    $this->conn = $db;
  }

  // Get all users
  public function read()
  {
    $query = "SELECT * FROM " . $this->table_name;
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // Get all users by role
  public function readByRole($role)
  {
    $query = "SELECT * FROM " . $this->table_name . " WHERE role = :role";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':role', $role);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // Get an user by ID
  public function readOne($id)
  {
    $query = "SELECT * FROM users WHERE id = :id LIMIT 1";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  // Authenticate user
  public function authenticate($email, $password)
  {
    $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($password, $row['password'])) {
      $this->id = $row['id'];
      $this->name = $row['name'];
      $this->email = $row['email'];
      $this->role = $row['role'];
      $this->created_at = $row['created_at'];
      $this->profile_pic = $row['profile_pic'];
      return true;
    }

    return false;
  }

  // Create user
  public function create($name, $email, $role, $password, $profile_pic = null)
  {
    $query = "INSERT INTO " . $this->table_name . " (name, email, password, role, created_at";

    if ($profile_pic) {
      $query .= ", profile_pic";
    }

    $query .= ") VALUES (:name, :email, :password, :role, :created_at";

    if ($profile_pic) {
      $query .= ", :profile_pic";
    }

    $query .= ")";

    $stmt = $this->conn->prepare($query);

    $this->name = htmlspecialchars(strip_tags($name));
    $this->email = htmlspecialchars(strip_tags($email));
    $this->password = password_hash($password, PASSWORD_DEFAULT);
    $this->role = htmlspecialchars(strip_tags($role));
    $this->created_at = date('Y-m-d H:i:s');

    $stmt->bindParam(":name", $this->name);
    $stmt->bindParam(":email", $this->email);
    $stmt->bindParam(":password", $this->password);
    $stmt->bindParam(":role", $this->role);
    $stmt->bindParam(":created_at", $this->created_at);

    if ($profile_pic) {
      $stmt->bindParam(":profile_pic", $profile_pic);
    }

    if ($stmt->execute()) {
      return true;
    }

    return false;
  }

  // Update user
  public function update($id, $name = null, $email = null, $role = null, $password = null, $profile_pic = null)
  {
    $query = "UPDATE " . $this->table_name . " SET ";
    $fields = [];
    $params = [];

    if ($name !== null) {
      $fields[] = "name = :name";
      $params[':name'] = htmlspecialchars(strip_tags($name));
    }

    if ($email !== null) {
      $fields[] = "email = :email";
      $params[':email'] = htmlspecialchars(strip_tags($email));
    }

    if ($role !== null) {
      $fields[] = "role = :role";
      $params[':role'] = htmlspecialchars(strip_tags($role));
    }

    if ($password !== null) {
      $fields[] = "password = :password";
      $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    if ($profile_pic !== null) {
      $fields[] = "profile_pic = :profile_pic";
      $params[':profile_pic'] = $profile_pic;
    }

    if (empty($fields)) {
      return false;
    }

    $query .= implode(', ', $fields);
    $query .= " WHERE id = :id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    foreach ($params as $param => $value) {
      $stmt->bindValue($param, $value);
    }

    return $stmt->execute();
  }

  public function delete($id)
  {
    try {
      // Start transaction
      $this->conn->beginTransaction();

      // Deasignate the ticket asignation from the user we are deleting
      $query = "UPDATE tickets SET assigned_to = NULL WHERE assigned_to = :id";
      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(":id", $id);
      $stmt->execute();

      // Deleting user
      $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(":id", $id);
      $stmt->execute();

      // Confirm transaction
      $this->conn->commit();
      return true;
    } catch (Exception $e) {
      // If something went wrong, do a rollback
      $this->conn->rollBack();
      throw $e;
    }
  }
}
