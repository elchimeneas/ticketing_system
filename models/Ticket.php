<?php
class Ticket
{
  private $conn;
  private $table_name = "tickets";

  public $id;
  public $subject;
  public $message;
  public $category_id;
  public $created_by;
  public $assigned_to;
  public $status;
  public $created_at;
  public $updated_at;

  public function __construct($db)
  {
    $this->conn = $db;
  }

  // Obtener todos los tickets
  public function read()
  {
    $query = "SELECT t.id, t.subject, t.message, t.status, t.created_at, t.updated_at, 
                        c.name as category_name, 
                        u1.name as created_by_name, 
                        u2.name as assigned_to_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN categories c ON t.category_id = c.id
                  LEFT JOIN users u1 ON t.created_by = u1.id
                  LEFT JOIN users u2 ON t.assigned_to = u2.id
                  ORDER BY t.created_at DESC";

    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
  }

  // Obtener tickets por estado
  public function readByStatus($status)
  {
    $query = "SELECT t.id, t.subject, t.message, t.status, t.created_at, t.updated_at, 
                        c.name as category_name, 
                        u1.name as created_by_name, 
                        u2.name as assigned_to_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN categories c ON t.category_id = c.id
                  LEFT JOIN users u1 ON t.created_by = u1.id
                  LEFT JOIN users u2 ON t.assigned_to = u2.id
                  WHERE t.status = ?
                  ORDER BY t.created_at DESC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $status);
    $stmt->execute();
    return $stmt;
  }

  // Obtener tickets por usuario creador
  public function readByCreator($user_id)
  {
    $query = "SELECT t.id, t.subject, t.message, t.status, t.created_at, t.updated_at, 
                        c.name as category_name, 
                        u1.name as created_by_name, 
                        u2.name as assigned_to_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN categories c ON t.category_id = c.id
                  LEFT JOIN users u1 ON t.created_by = u1.id
                  LEFT JOIN users u2 ON t.assigned_to = u2.id
                  WHERE t.created_by = ?
                  ORDER BY t.created_at DESC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $user_id);
    $stmt->execute();
    return $stmt;
  }

  // Obtener un ticket por ID
  public function readOne($id): ?array
  {
    $query = "SELECT t.id, t.subject, t.message, t.status, t.created_at, t.updated_at, 
                        c.name as category_name, 
                        u1.name as created_by_name, 
                        u2.name as assigned_to_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN categories c ON t.category_id = c.id
                  LEFT JOIN users u1 ON t.created_by = u1.id
                  LEFT JOIN users u2 ON t.assigned_to = u2.id
                  WHERE t.id = ?";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $id);
    $stmt->execute();

    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $row;
  }

  // Crear un nuevo ticket
  public function create($subject, $category, $message, $created_by)
  {
    $query = "INSERT INTO " . $this->table_name . " 
                  SET subject=:subject, message=:message, category_id=:category_id, 
                      created_by=:created_by, status=:status, created_at=:created_at";

    $stmt = $this->conn->prepare($query);

    // Sanitizar datos
    $this->subject = htmlspecialchars(strip_tags($this->subject));
    $this->message = htmlspecialchars(strip_tags($this->message));
    $this->category_id = htmlspecialchars(strip_tags($this->category_id));
    $this->created_by = htmlspecialchars(strip_tags($this->created_by));
    $this->status = 'pending'; // Estado inicial: pendiente
    $this->created_at = date('Y-m-d H:i:s');

    // Vincular valores
    $stmt->bindParam(":subject", $subject);
    $stmt->bindParam(":message", $message);
    $stmt->bindParam(":category_id", $category);
    $stmt->bindParam(":created_by", $created_by);
    $stmt->bindParam(":status", $this->status);
    $stmt->bindParam(":created_at", $this->created_at);

    if ($stmt->execute()) {
      return $this->conn->lastInsertId();
    }

    return false;
  }

  // Asignar ticket
  public function assign($id, $assigned_to)
  {
    $query = "UPDATE " . $this->table_name . " 
                  SET assigned_to=:assigned_to
                  WHERE id=:id";

    $stmt = $this->conn->prepare($query);

    // Sanitizar datos
    $this->assigned_to = htmlspecialchars(strip_tags($this->assigned_to));

    // Vincular valores
    $stmt->bindParam(":assigned_to", $assigned_to);
    $stmt->bindParam(":id", $id);

    if ($stmt->execute()) {
      return true;
    }

    return false;
  }

  // Actualizar estado del ticket
  public function updateStatus($id, $new_status)
  {
    $query = "UPDATE " . $this->table_name . " 
                  SET status=:status, updated_at=:updated_at 
                  WHERE id=:id";

    $stmt = $this->conn->prepare($query);

    // Sanitizar datos
    $this->status = htmlspecialchars(strip_tags($this->status));
    $this->updated_at = date('Y-m-d H:i:s');

    // Vincular valores
    $stmt->bindParam(":status", $new_status);
    $stmt->bindParam(":updated_at", $this->updated_at);
    $stmt->bindParam(":id", $id);

    if ($stmt->execute()) {
      return true;
    }

    return false;
  }

  // Actualizar categoría
  public function updateCategory($id, $new_category)
  {
    $query = "UPDATE " . $this->table_name . " 
                  SET category_id=:category_id, updated_at=:updated_at 
                  WHERE id=:id";

    $stmt = $this->conn->prepare($query);

    // Sanitizar datos
    $this->category_id = htmlspecialchars(strip_tags($this->category_id));
    $this->updated_at = date('Y-m-d H:i:s');

    // Vincular valores
    $stmt->bindParam(":category_id", $new_category);
    $stmt->bindParam(":updated_at", $this->updated_at);
    $stmt->bindParam(":id", $id);

    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  public function getCountByStatus($status)
  {
    $query = "SELECT COUNT(*) as total 
            FROM " . $this->table_name . " 
            WHERE status = :status";

    $stmt = $this->conn->prepare($query);

    // Sanitizar datos
    $this->status = htmlspecialchars(strip_tags($this->status));

    // Vincular valores
    $stmt->bindParam(":status", $status);
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $row;
  }

  public function delete($id)
  {
    $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":id", $id);
    return $stmt->execute();
  }
}
