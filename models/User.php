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

  // Obtener todos los usuarios
  public function read()
  {
    $query = "SELECT * FROM " . $this->table_name;
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // Obtener usuarios por rol
  public function readByRole($role)
  {
    $query = "SELECT * FROM " . $this->table_name . " WHERE role = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $role);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // Obtener un usuario por ID
  public function readOne($id)
  {
    $query = "SELECT * FROM users WHERE id = :id LIMIT 1";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  // Autenticar usuario
  public function authenticate($email, $password)
  {
    $query = "SELECT * FROM " . $this->table_name . " WHERE email = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $email);
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

  // Crear un nuevo usuario
  public function create($name, $email, $role, $password, $profile_pic = null)
  {
    $query = "INSERT INTO " . $this->table_name . " SET name=:name, email=:email, password=:password, role=:role, created_at=:created_at";

    if ($profile_pic) {
      $query .= ", profile_pic=:profile_pic";
    }

    $stmt = $this->conn->prepare($query);

    // Sanitizar datos
    $this->name = htmlspecialchars(strip_tags($name));
    $this->email = htmlspecialchars(strip_tags($email));
    $this->password = password_hash($password, PASSWORD_DEFAULT);
    $this->role = htmlspecialchars(strip_tags($role));
    $this->created_at = date('Y-m-d H:i:s');

    // Vincular valores
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
