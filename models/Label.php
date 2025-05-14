<?php
class Label
{
  private $conn;
  private $table_name = "labels";

  public $id;
  public $name;
  public $color;

  public function __construct($db)
  {
    $this->conn = $db;
  }

  // Get all labels
  public function read()
  {
    $query = "SELECT * FROM " . $this->table_name;
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt;
  }

  // Get a label by ticket
  public function readByTicket($ticket_id)
  {
    $query = "SELECT l.* FROM " . $this->table_name . " l
                  JOIN ticket_labels tl ON l.id = tl.label_id
                  WHERE tl.ticket_id = :ticket_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':ticket_id', $ticket_id);
    $stmt->execute();
    return $stmt;
  }

  // Assign label to ticket
  public function assignToTicket($ticket_id)
  {
    $query = "INSERT INTO ticket_labels SET ticket_id=:ticket_id, label_id=:label_id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":ticket_id", $ticket_id);
    $stmt->bindParam(":label_id", $this->id);

    if ($stmt->execute()) {
      return true;
    }

    return false;
  }

  // Remove label from ticket
  public function removeFromTicket($ticket_id)
  {
    $query = "DELETE FROM ticket_labels WHERE ticket_id=:ticket_id AND label_id=:label_id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":ticket_id", $ticket_id);
    $stmt->bindParam(":label_id", $this->id);

    if ($stmt->execute()) {
      return true;
    }

    return false;
  }
}
