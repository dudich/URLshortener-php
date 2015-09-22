<?php
if (!defined("DIRECT_ACCESS")) die('No direct access');

class Storage
{
    protected static $checkUrlExists = true;
    private $table_scheme = "CREATE TABLE IF NOT EXISTS links ( id INTEGER PRIMARY KEY, link TEXT NOT NULL UNIQUE)";
    private $file_db;

    public function __construct()
    {

        $this->file_db = new PDO('sqlite:db/links.sqlite3');
        // Set errormode to exceptions
        $this->file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->file_db->exec($this->table_scheme);

    }

    public function urlToShortCode($link)
    {

        if (empty($link)) {
            throw new Exception("No URL was supplied.");
        }

        if ($this->validateUrlFormat($link) == false) {
            throw new Exception(
                "URL does not have a valid format.");
        }

        if (self::$checkUrlExists) {
            if (!$this->verifyUrlExists($link)) {
                throw new Exception(
                    "URL does not appear to exist.");
            }
        }

        $shortCode = $this->getUrlFromDbByLink($link);

        if ($shortCode == false) {

            $insert = "INSERT INTO links (link) VALUES (:link)";
            $stmt = $this->file_db->prepare($insert);
            $stmt->bindParam(':link', $link);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $shortCode = $this->getUrlFromDbByLink($link);
        }
        $this->file_db = null;
        return PseudoCrypt::hash($shortCode['id'], 6);
    }

    protected function validateUrlFormat($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL,
            FILTER_FLAG_HOST_REQUIRED);
    }

    protected function verifyUrlExists($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return (!empty($response) && $response != 404);
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
        return (empty($result)) ? false : $result;
    }

    public function getUrlFromDbByHash($hash)
    {
        $url = $this->getUrlFromDbById(PseudoCrypt::unhash($hash));
        $this->file_db = null;
        return $url;
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
        return (empty($result)) ? false : $result;
    }
}