<?php
namespace S3UTaxonomy\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Persisttence\ObjectManager;
/**
* @ORM\Entity
* @ORM\Table(name="zf_term_taxonomy")
*/
class ZfTermTaxonomy
{
	/**
	* @ORM\Column(name="term_taxonomy_id",type="bigint",length=20)
	* @ORM\Id
	* @ORM\GeneratedValue
	*/
	private $termTaxonomyId;

	/**
	* @ORM\ManyToOne(targetEntity="S3UTaxonomy\Entity\ZfTerm", cascade={"persist"})
	* @ORM\JoinColumn(name="term_id", referencedColumnName="term_id")
	*/
	private $termId;

	/**
	 * @ORM\Column(length=200)
	 */
	private $taxonomy;

	/**
	 * @ORM\Column(type="text")
	 */
	private $description;

	/**
	* @ORM\Column(type="bigint",length=20)
	* @ORM\ManyToOne(targetEntity="S3UTaxonomy\Entity\ZfTermTaxonomy")
	* @ORM\JoinColumn(name="parent", referencedColumnName="term_taxonomy_id", nullable=true)
	*/
	private $parent;


	/**
	* @ORM\Column(type="bigint",length=20)
	*/
	private $count;

	private $cap;


	public function setCap($cap)
	{
		$this->cap=$cap;
	}

	public function getCap()
	{
		return $this->cap;
	}


	public function setTermTaxonomyId($termTaxonomyId)
	{
		$this->termTaxonomyId=$termTaxonomyId;
	}
	public function getTermTaxonomyId()
	{
		return $this->termTaxonomyId;
	}

	public function setTermId($termId)
	{
		$this->termId=$termId;
	}

	public function getTermId()
	{
		return $this->termId;
	}

	public function setTaxonomy($taxonomy)
	{
		$this->taxonomy=$taxonomy;
	}

	public function getTaxonomy()
	{
		return $this->taxonomy;
	}

	public function setDescription($description)
	{
		$this->description=$description;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function setParent($parent)
	{
		$this->parent=$parent;
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function setCount($count)
	{
		$this->count=$count;
	}

	public function getCount()
	{
		return $this->count;
	}

	public function getNameParent()
	{
		if($this->getParent)
		{
		$parent=$this->parent;
		if($parent)
			return $parent->getTermId();
		}
	}
}
?>