<?php
class Settings
{
  private $conn;
  private $table_name = "settings";

  public $site_name;
  public $admin_email;

  public function __construct($db)
  {
    $this->conn = $db;
  }

  public function read()
  {
    $query = "SELECT * FROM $this->table_name";

    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $results;
  }

  public function updatePageTitle($new_site_name)
  {
    $query = "UPDATE $this->table_name SET site_name =:site_name";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":site_name", $new_site_name);

    if ($stmt->execute()) {
      return true;
    }

    return false;
  }

  public function updateAdminEmail($new_admin_email)
  {
    $query = "UPDATE $this->table_name SET admin_email =:admin_email";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":admin_email", $new_admin_email);

    if ($stmt->execute()) {
      return true;
    }

    return false;
  }
}
