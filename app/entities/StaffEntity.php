<?php
/**
 * Created by dFramework.
 * Date: 29/12/2019 - 23:47:33
 * Entity: StaffEntity
 */

class StaffEntity
{
    /**
     * @var int
     */
    private $idMembre;
    /**
     *@return int
     */
    public function getIdMembre() : int {
        return $this->idMembre;
    }
    /**
     * @param int $idMembre
     * @return StaffEntity
     */
    public function setIdMembre(int $idMembre) : self {
        $this->idMembre = $idMembre;
        return $this;
    }


    /**
     * @var string
     */
    private $poste;
    /**
     *@return string
     */
    public function getPoste() : string {
        return $this->poste;
    }
    /**
     * @param string $poste
     * @return StaffEntity
     */
    public function setPoste(string $poste) : self {
        $this->poste = $poste;
        return $this;
    }

    /**
     * @var int
     */
    private $grade = 0;
    /**
     *@return int
     */
    public function getGrade() : int {
        return $this->grade;
    }
    /**
     * @param int $grade
     * @return StaffEntity
     */
    public function setGrade(int $grade) : self {
        $this->grade = $grade;
        return $this;
    }

    /**
     * @var string
     */
    private $description;
    /**
     *@return string
     */
    public function getDescription() : string {
        return $this->description;
    }
    /**
     * @param string $description
     * @return StaffEntity
     */
    public function setDescription(string $description) : self {
        $this->description = $description;
        return $this;
    }

    /**
     * @var string
     */
    private $nomMembre;
    /**
     *@return string
     */
    public function getNomMembre() : string {
        return $this->nomMembre;
    }
    /**
     * @param string $nomMembre
     * @return StaffEntity
     */
    public function setNomMembre(string $nomMembre) : self {
        $this->nomMembre = $nomMembre;
        return $this;
    }

    /**
     * @var string
     */
    private $prenomMembre;
    /**
     *@return string
     */
    public function getPrenomMembre() : string {
        return $this->prenomMembre;
    }
    /**
     * @param string $prenomMembre
     * @return StaffEntity
     */
    public function setPrenomMembre(string $prenomMembre) : self {
        $this->prenomMembre = $prenomMembre;
        return $this;
    }

    /**
     * @var string
     */
    private $competence;
    /**
     *@return string
     */
    public function getCompetence() : string {
        return $this->competence;
    }
    /**
     * @param string $competence
     * @return StaffEntity
     */
    public function setCompetence(?string $competence = null) : self {
        $this->competence = $competence;
        return $this;
    }


    /*** FONCTIONS PROPRES ***/

    public function avatar() {
        return img_url('staff/'.md5($this->idMembre).'.jpg');
    }

    public function url() {
        return site_url('portfolio/'.scl_moveSpecialChar($this->profil()));
    }

    public function profil() {
        return ucwords(htmlentities($this->prenomMembre.' '.$this->nomMembre));
    }

}