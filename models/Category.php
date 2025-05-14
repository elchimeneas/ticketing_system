<?php
class Category
{
  private $conn;
  private $table_name = "categories";

  public $id;
  public $name;
  public $description;

  public function __construct($db)
  {
    $this->conn = $db;
  }

  // Get all categories
  public function read()
  {
    $query = "SELECT * FROM " . $this->table_name;
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
  }

  // Get category by ID
  public function readOne()
  {
    $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $this->id);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      $this->name = $row['name'];
      $this->description = $row['description'];
      return true;
    }

    return false;
  }

  // Create new category
  public function create()
  {
    $query = "INSERT INTO " . $this->table_name . " SET name=:name, description=:description";

    $stmt = $this->conn->prepare($query);

    $this->name = htmlspecialchars(strip_tags($this->name));
    $this->description = htmlspecialchars(strip_tags($this->description));

    $stmt->bindParam(":name", $this->name);
    $stmt->bindParam(":description", $this->description);

    if ($stmt->execute()) {
      return true;
    }

    return false;
  }
}
