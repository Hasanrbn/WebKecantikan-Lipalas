<?php
/**
 * Penyimpanan session di database (tabel php_session).
 *
 * Dipakai hanya bila config 'session' = 'db' (LIPALAS_SESSION=db). Di hosting serverless seperti
 * Vercel, berkas session di disk tidak dibagi antar-instance dan hilang, sehingga login akan sering
 * "terlepas". Dengan tabel ini, session tetap ada di mana pun fungsi dijalankan.
 * Di XAMPP / hosting biasa tidak perlu: pakai bawaan PHP ('file').
 */
class LipalasDbSessionHandler implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        try {
            $min = time() - $this->lifetime();
            $stmt = db()->prepare('SELECT data FROM php_session WHERE id = ? AND updated_at >= ? LIMIT 1');
            $stmt->bind_param('si', $id, $min);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $row ? (string) $row['data'] : '';
        } catch (Throwable $t) {
            error_log('[Lipalas] session read: ' . $t->getMessage());
            return '';
        }
    }

    public function write(string $id, string $data): bool
    {
        if ($data === '') {   // session kosong: hapus barisnya (pastikan data lama tidak tersisa)
            return $this->destroy($id);
        }
        try {
            $now = time();
            $stmt = db()->prepare(
                'INSERT INTO php_session (id, data, updated_at) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE data = VALUES(data), updated_at = VALUES(updated_at)'
            );
            $stmt->bind_param('ssi', $id, $data, $now);
            $stmt->execute();
            $stmt->close();
            return true;
        } catch (Throwable $t) {
            error_log('[Lipalas] session write: ' . $t->getMessage());
            return false;
        }
    }

    public function destroy(string $id): bool
    {
        try {
            $stmt = db()->prepare('DELETE FROM php_session WHERE id = ?');
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $stmt->close();
        } catch (Throwable $t) {
            error_log('[Lipalas] session destroy: ' . $t->getMessage());
        }
        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        try {
            $min = time() - $this->lifetime();
            $stmt = db()->prepare('DELETE FROM php_session WHERE updated_at < ?');
            $stmt->bind_param('i', $min);
            $stmt->execute();
            $n = $stmt->affected_rows;
            $stmt->close();
            return $n;
        } catch (Throwable $t) {
            return 0;
        }
    }

    /** Dipanggil saat use_strict_mode aktif: hanya terima ID yang memang ada. */
    public function validateId(string $id): bool
    {
        try {
            $min = time() - $this->lifetime();
            $stmt = db()->prepare('SELECT 1 FROM php_session WHERE id = ? AND updated_at >= ? LIMIT 1');
            $stmt->bind_param('si', $id, $min);
            $stmt->execute();
            $ok = $stmt->get_result()->num_rows > 0;
            $stmt->close();
            return $ok;
        } catch (Throwable $t) {
            return false;
        }
    }

    public function updateTimestamp(string $id, string $data): bool
    {
        return $this->write($id, $data);
    }

    private function lifetime(): int
    {
        return 2 * 60 * 60;   // sama dengan batas idle 2 jam di helpers.php
    }
}
