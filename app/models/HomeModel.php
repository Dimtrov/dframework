<?php
/**
 * Created by PhpStorm.
 * User: Dimitri Sitchet
 * Date: 22/12/2019
 * Time: 18:14
 */

class HomeModel extends \dFramework\core\Model
{
    public function __construct()
    {
        parent::__construct();
    }


    public function m()
    {
        $this->migrator()->down('1.0');
    }


    public function getStaff($id_staff = null)
    {
        $this->free_db()
            ->select()
            ->from('staff')
            ->join('profils_membres', 'profils_membres.id_membre = staff.id_membre')
            ->order('grade', 'DESC');
        if(null !== $id_staff) {
            $this->where('id_staff = ?')->params([$id_staff]);
        }
        return $this->result(DF_FCLA, Staff::class);
    }
}