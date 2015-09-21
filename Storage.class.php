<?php

/**
 * Created by PhpStorm.
 * Date: 21.09.15
 * Time: 18:06
 */
class Storage
{
    private $table_scheme = "CREATE TABLE IF NOT EXISTS links ( id INTEGER PRIMARY KEY, link TEXT NOT NULL UNIQUE)";
    private $file_db;

    public function __construct()
    {
        try {
            $this->file_db = new PDO('sqlite:db/links.sqlite3');
            // Set errormode to exceptions
            $this->file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->file_db->exec($this->table_scheme);
        } catch (PDOException $e) {
            echo 'error';
        }
    }

    public function urlToShortCode($link)
    {
        try {
            $insert = "INSERT INTO links (link) VALUES (:link)";
            $stmt = $this->file_db->prepare($insert);
            $stmt->bindParam(':link', $link);
            $stmt->execute();
            $stmt->fetch(PDO::FETCH_ASSOC);
            return PseudoCrypt::hash($this->getUrlFromDbByLink($link)['id'], 6);
        } catch (PDOException $e) {
            return PseudoCrypt::hash($this->getUrlFromDbByLink($link)['id'], 6);
        }
    }

    private function getUrlFromDbByLink($link)
    {
        $query = "SELECT id, link FROM links WHERE link = :link LIMIT 1";
        $stmt = $this->file_db->prepare($query);
        $params = array(
            "link" => $link
        );
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->file_db = null;
        return (empty($result)) ? false : $result;
    }

    public function getUrlFromDbByHash($hash)
    {
        $id = PseudoCrypt::unhash($hash);
        return $this->getUrlFromDbById($id);
    }

    private function getUrlFromDbById($id)
    {
        $query = "SELECT id, link FROM links WHERE id = :id LIMIT 1";
        $stmt = $this->file_db->prepare($query);
        $params = array(
            "id" => $id
        );
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->file_db = null;
        return (empty($result)) ? false : $result;
    }

}