<?php
class WorksModel extends \dFramework\core\Model
{
    public function __construct()
    {
        parent::__construct();
    }


    public function getWorks()
    {
        return $this->free_db()
            ->select()
            ->from('travaux')
            ->result();
    }
}