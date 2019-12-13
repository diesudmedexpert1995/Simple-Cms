<?php


class Article
{
    public $id = null;
    public $publicationDate = null;
    public $title = null;
    public $summary = null;
    public $content = null;

    /**
     * Article constructor.
     * @param array $data
     */
    public function __construct($data = array())
    {
        if (isset($data['id'])) $this->id = (int)$data['id'];
        if (isset($data['publicationDate'])) $this->publicationDate = (int)$data['publicationDate'];
        if (isset($data['title'])) $this->title = preg_replace("/[^\.\,\-\_\'\"\@\?\!\:\$ a-zA-Z0-9()]/","", $data['title']);
        if (isset($data['summary'])) $this->summary = preg_replace("/[^\.\,\-\_\'\"\@\?\!\:\$ a-zA-Z0-9()]/","", $data['summary']);
        if (isset($data['content'])) $this->content = $data['content'];
    }

    public function storeFromValues($params){
        $this->__construct($params);
        if (isset($params['publicationDate'])){
            $publicationDate = explode('-',$params['publicationDate']);
            if (count($publicationDate) == 3){
                list($y,$m,$d) = $publicationDate;
                $this->publicationDate = mktime(0,0,0,$m,$d,$y);
            }
        }
    }

    public static function getById($id){
        $connection = new PDO(DB_DSN, DB_USERNAME,DB_PASSWORD);
        $rawQuery = "SELECT *, UNIX_TIMESTAMP(publicationDate) AS publicationDate FROM articles WHERE id = :id";
        $statement = $connection->prepare($rawQuery);
        $statement->bindValue(":id",$id,PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch();
        $connection = null;
        if($row) return new Article($row);
    }

    public static function getList($numRows=1000000, $order="publicationDate DESC"){
        $connection = new PDO(DB_DSN, DB_USERNAME,DB_PASSWORD);
        $rawQuery = "SELECT SQL_CALC_FOUND_ROWS *, UNIX_TIMESTAMP(publicationDate) AS publicationDate FROM articles  ORDER BY " . mysql_escape_string($order) . " LIMIT :numRows";
        $statement = $connection->prepare($rawQuery);
        $statement->bindValue(":numRows",$numRows,PDO::PARAM_INT);
        $statement->execute();
        $list = array();

        while ($row = $statement->fetch()){
            $article = new Article($row);
            $list[] = $article;
        }
        $sqlTotalRow = "SELECT FOUND_ROWS() AS totalRows";
        $totalRows = $connection->query($sqlTotalRow)->fetch();
        $connection = null;
        return (array("results"=>$list,"totalRows"=>$totalRows[0]));
    }

    public function insert()
    {
        if(!is_null($this->id))
            trigger_error("Article::insert(): Attempt to insert an Article object that already has its ID property set (to $this->id.",E_USER_ERROR);
        $connection = new PDO(DB_DSN, DB_USERNAME,DB_PASSWORD);
        $rawQuery = "INSERT INTO articles ( publicationDate, title, summary, content ) VALUES ( FROM_UNIXTIME(:publicationDate), :title, :summary, :content )";
        $statement = $connection->prepare($rawQuery);
        $statement->bindValue( ":publicationDate", $this->publicationDate, PDO::PARAM_INT );
        $statement->bindValue( ":title", $this->title, PDO::PARAM_STR );
        $statement->bindValue( ":summary", $this->summary, PDO::PARAM_STR );
        $statement->bindValue( ":content", $this->content, PDO::PARAM_STR );
        $statement->execute();
        $this->id = $connection->lastInsertId();
        $connection = null;
    }
    
    public function update()
    {
        if(is_null($this->id)){
            trigger_error("Article::update(): Attempt to update an Article object that does not have its ID property set.",E_USER_ERROR);
        }
        $connection = new PDO(DB_DSN, DB_USERNAME,DB_PASSWORD);
        $rawQuery = "UPDATE articles SET publicationDate=FROM_UNIXTIME(:publicationDate), title=:title, summary=:summary, content=:content WHERE id = :id";
        $statement = $connection->prepare($rawQuery);
        $statement->bindValue( ":publicationDate", $this->publicationDate, PDO::PARAM_INT );
        $statement->bindValue( ":title", $this->title, PDO::PARAM_STR );
        $statement->bindValue( ":summary", $this->summary, PDO::PARAM_STR );
        $statement->bindValue( ":content", $this->content, PDO::PARAM_STR );
        $statement->bindValue( ":id", $this->id, PDO::PARAM_INT);
        $statement->execute();
        $connection=null;

    }

    public function delete()
    {
        if(is_null($this->id)){
            trigger_error("Article::delete(): Attempt to delete an Article object that does not have its ID property set.",E_USER_ERROR);
        }
        $connection = new PDO(DB_DSN, DB_USERNAME,DB_PASSWORD);
        $statement = $connection->prepare("DELETE FROM articles WHERE id = :id LIMIT 1" );
        $statement->bindValue(":id", $this->id, PDO::PARAM_INT);
        $statement->execute();
        $connection = null;

    }
}