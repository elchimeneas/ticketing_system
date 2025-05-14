<?php
class Database
{
  private $host = "aws-0-eu-west-2.pooler.supabase.com";
  private $db_name = "postgres";
  private $username = "postgres.auktrmhxykcwiprrtmzr";
  private $password = "alumno";
  private $port = 6543;
  public $conn;

  public function getConnection()
  {
    $this->conn = null;

    try {
      $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}";
      $this->conn = new PDO($dsn, $this->username, $this->password);
    } catch (PDOException $exception) {
      echo "Error de conexión: " . $exception->getMessage();
    }

    return $this->conn;
  }
}
