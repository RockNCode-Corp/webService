<?php
/**
 * Created by PhpStorm.
 * User: Apomalyn
 * Date: 2017-07-31
 * Time: 21:17
 */

class Session extends DataBase implements SessionHandlerInterface {

    public function close() {
        return true;
    }

    public function destroy($session_id) {
        $sql = 'DELETE FROM session WHERE id = :id';
        $this->execute($sql, array(':id' => $session_id), true);
    }

    public function gc($maxlifetime) {
        $sql = 'DELETE FROM session WHERE lastAccessAt < :tenMinBefore AND createdAt < :oneDayBefore';

        $this->execute($sql, array(
            ':tenMinBefore' => date('Y-m-d H:i:s', strtotime('-10 minutes')),
            ':oneDayBefore' => date('Y-m-d H:i:s', strtotime('-1 days'))
        ), true);

        return true;
    }

    public function open($save_path, $name) {
        return true;
    }

    public function read($session_id) {
        $sql = 'SELECT data FROM session WHERE id = :id AND lastAccessAt > :tenMinBefore AND createdAt > :oneDayBefore';

        $result = $this->execute($sql, array(
            ':id' => $session_id,
            ':tenMinBefore' => date('Y-m-d H:i:s', strtotime('-10 minutes')),
            ':oneDayBefore' => date('Y-m-d H:i:s', strtotime('-1 days'))
        ));

        if ($result["row_count"]) {
            return $result['data'];
        }
        return '';
    }

    public function write($session_id, $data) {
        $check = 'SELECT id FROM session WHERE id = :id AND lastAccessAt > :tenMinBefore';
        $result = $this->execute($check, array(
            ':id' => $session_id,
            ':tenMinBefore' => date('Y-m-d H:i:s', strtotime('-10 minutes'))
        ));

        if (!$result) {
            $sql = 'INSERT INTO session (id, createdAt, lastAccessAt) VALUES (:id, :now, :now);';

            $this->execute($sql, array(
                ':id' => $session_id,
                ':now' => date('Y-m-d H:i:s')
            ), true);
        } else {
            $sql = 'UPDATE session SET data = :data, lastAccessAt = :now WHERE id=:id';

            $this->execute($sql, array(
                ':data' => $data,
                ':now' => date('Y-m-d H:i:s'),
                ':id' => $session_id
            ), true);
        }
        return true;
    }
}