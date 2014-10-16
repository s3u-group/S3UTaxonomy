<?php
namespace S3UTaxonomy\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
* @ORM\Entity
* @ORM\Table(name="zf_term_taxonomy")
*/
class ZfTermTaxonomy
{
	/**
	* @ORM\Column(type="bigint",length=20)
	* @ORM\Id
	* @ORM\GeneratedValue
	*/
	private $term_taxonomy_id;

	/**
	* @ORM\Column(type="bigint",length=20)
	* @ORM\ManyToOne(targetEntity="S3UTaxonomy\Entity\ZfTerm")
	* @ORM\JoinColumn(name="term_id", referencedColumnName="term_id")
	*/
	private $term_id=0;

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


	public function getTermTaxonomyId()
	{
		return $this->term_taxonomy_id;
	}

	public function setTermId($term_id)
	{
		$this->term_id=$term_id;
	}

	public function getTermId()
	{
		return $this->term_id;
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
}
?>