<?php
require __DIR__ . '/../config/database.php';

class ClientController {
    private $pdo;
    private $redis;

    public function __construct($pdo, $redis) {
        $this->pdo = $pdo;
        $this->redis = $redis;
    }

    public function getAllClients() {
        $cachedClients = $this->redis->get('clients_all');
        if ($cachedClients) {
            return json_decode($cachedClients, true);
        }

        $stmt = $this->pdo->query("SELECT * FROM my_client WHERE deleted_at IS NULL");
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->redis->set('clients_all', json_encode($clients));

        return $clients;
    }

    public function createClient($data) {
        $stmt = $this->pdo->prepare("INSERT INTO my_client (name, slug, client_prefix) VALUES (?, ?, ?)");
        $stmt->execute([$data['name'], $data['slug'], $data['client_prefix']]);

        $this->redis->set($data['slug'], json_encode($data));

        return "Client Created";
    }

    public function updateClient($slug, $data) {
        $stmt = $this->pdo->prepare("UPDATE my_client SET name = ?, client_prefix = ?, updated_at = NOW() WHERE slug = ?");
        $stmt->execute([$data['name'], $data['client_prefix'], $slug]);

        $this->redis->del($slug);
        $this->redis->set($slug, json_encode($data));

        return "Client Updated";
    }

    public function deleteClient($slug) {
        $stmt = $this->pdo->prepare("UPDATE my_client SET deleted_at = NOW() WHERE slug = ?");
        $stmt->execute([$slug]);

        $this->redis->del($slug);

        return "Client Deleted";
    }
}
?>
