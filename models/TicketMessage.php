<?php
class TicketMessage
{
    private $conn;
    private $table_name = "ticket_messages";

    public $id;
    public $ticket_id;
    public $user_id;
    public $message;
    public $created_at;
    public $can_reply;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Get all messages for a ticket
    public function getTicketMessages($ticket_id)
    {
        $query = "SELECT m.*, u.name as user_name, u.role as user_role, u.profile_pic 
                 FROM " . $this->table_name . " m 
                 LEFT JOIN users u ON m.user_id = u.id 
                 WHERE m.ticket_id = ? 
                 ORDER BY m.created_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $ticket_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create a new message
    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                 (ticket_id, user_id, message, can_reply) 
                 VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->message = htmlspecialchars(strip_tags($this->message));

        // Bind data
        $stmt->bindParam(1, $this->ticket_id);
        $stmt->bindParam(2, $this->user_id);
        $stmt->bindParam(3, $this->message);
        $stmt->bindParam(4, $this->can_reply);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get the last message of a ticket
    public function getLastMessage($ticket_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE ticket_id = ? 
                 ORDER BY created_at DESC 
                 LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $ticket_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Check if user can reply
    public function canUserReply($ticket_id, $user_id, $user_role)
    {
        $last_message = $this->getLastMessage($ticket_id);

        if (!$last_message) {
            return true; // If no messages, user can reply
        }

        // Support and admin can always reply
        if ($user_role === 'support' || $user_role === 'administrator') {
            return true;
        }

        // Regular user can only reply if:
        // 1. Last message has can_reply = true
        // 2. Last message was not from themselves
        return $last_message['can_reply'] && $last_message['user_id'] !== $user_id;
    }
}
