<?php
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../vendor/autoload.php';




class ClientController {
    private $pdo;
    private $redis;

    public function __construct($pdo, $redis) {
        $this->pdo = $pdo;
        $this->redis = $redis;
    }

    public function getAllClients() {
        $cacheKey = 'clients_all';
        
        // Cek cache di Redis
        $cachedClients = $this->redis->get($cacheKey);
        if ($cachedClients) {
            return json_decode($cachedClients, true);
        }

        try {
            $stmt = $this->pdo->query("SELECT * FROM my_client WHERE deleted_at IS NULL");
            $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Simpan hasil ke Redis dengan waktu kedaluwarsa (misal: 1 jam)
            $this->redis->setex($cacheKey, 3600, json_encode($clients));

            return $clients;
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function createClient($data) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO my_client (name, slug, client_prefix) VALUES (?, ?, ?)");
            $stmt->execute([$data['name'], $data['slug'], $data['client_prefix']]);

            // Hapus cache agar data terbaru bisa diambil
            $this->redis->del('clients_all');

            return ['message' => "Client Created"];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function updateClient($slug, $data) {
        try {
            $stmt = $this->pdo->prepare("UPDATE my_client SET name = ?, client_prefix = ?, updated_at = NOW() WHERE slug = ?");
            $stmt->execute([$data['name'], $data['client_prefix'], $slug]);

            // Update cache
            $this->redis->del('clients_all');

            return ['message' => "Client Updated"];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function deleteClient($slug) {
        try {
            $stmt = $this->pdo->prepare("UPDATE my_client SET deleted_at = NOW() WHERE slug = ?");
            $stmt->execute([$slug]);

            // Hapus dari cache
            $this->redis->del('clients_all');

            return ['message' => "Client Deleted"];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
?>
