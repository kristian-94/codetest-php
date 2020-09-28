<?php

class Beer extends ActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function findWithBeerId($beerId)
    {
        return $this->find('beerId = :beerId', [':beerId' => $beerId]);
	}

	public function relations()
	{
		return [
			'hops' => array(self::MANY_MANY, 'Hop', 'beer_hop(beerId, hopId)'),
			'beerHops' => array(self::HAS_MANY, 'BeerHop', 'beerId'),
			'style' => array(self::BELONGS_TO, 'Style', 'styleId'),
			'yeasts' => array(self::MANY_MANY, 'Yeast', 'beer_yeast(beerId, yeastId)'),
			'beerYeasts' => array(self::HAS_MANY, 'BeerYeast', 'beerId'),
			'malts' => array(self::MANY_MANY, 'Malt', 'beer_malt(beerId, maltId)'),
			'beerMalts' => array(self::HAS_MANY, 'BeerMalt', 'beerId'),
		];
	}

	public function hasHop($hop)
	{
		foreach ($this->beerHops as $beerHop) {
			if ($beerHop->hopId === $hop->hopId) return $beerHop;
		}

		return false;
	}

	public function addHop($hop)
	{
		$beerHop = $this->hasHop($hop);

		if (!$beerHop) {
			$beerHop = new BeerHop();
			$beerHop->beerId = $this->beerId;
			$beerHop->hopId = $hop->hopId;
			$beerHop->save();
		}

		return $beerHop;
	}

	public function removeHop($hop) {
		$beerHop = $this->hasHop($hop);

		if ($beerHop) {
			$beerHop->delete();
		}
	}

	public function hasYeast($yeast)
	{
		foreach ($this->beerYeasts as $beerYeast) {
			if ($beerYeast->yeastId === $yeast->yeastId) return $beerYeast;
		}

		return false;
	}

	public function addYeast($yeast)
	{
		$beerYeast = $this->hasYeast($yeast);

		if (!$beerYeast) {
			$beerYeast = new BeerYeast();
			$beerYeast->beerId = $this->beerId;
			$beerYeast->yeastId = $yeast->yeastId;
			$beerYeast->save();
		}

		return $beerYeast;
	}

	public function removeYeast($yeast) {
		$beerYeast = $this->hasYeast($yeast);

		if ($beerYeast) {
			$beerYeast->delete();
		}
	}

	public function hasMalt($malt)
	{
		foreach ($this->beerMalts as $beerMalt) {
			if ($beerMalt->maltId === $malt->maltId) return $beerMalt;
		}

		return false;
	}

	public function addMalt($malt)
	{
		$beerMalt = $this->hasMalt($malt);

		if (!$beerMalt) {
			$beerMalt = new BeerMalt();
			$beerMalt->beerId = $this->beerId;
			$beerMalt->maltId = $malt->maltId;
			$beerMalt->save();
		}

		return $beerMalt;
	}

	public function removeMalt($malt) {
		$beerMalt = $this->hasMalt($malt);

		if ($beerMalt) {
			$beerMalt->delete();
		}
	}

    public function rules()
    {
        return array(
            // name is required
            array('name', 'required'),
            // Must be numeric.
            array('styleId,abv,ibu,isOrganic,year', 'numeric'),
            // Must be 6 characters
            array('beerId', 'primarykeylength'),
        );
    }

    function primarykeylength($attribute) {
        $keyattribute = $this->$attribute;
        if (isset($keyattribute) && is_string($keyattribute) && strlen($keyattribute) !== 6) {
            $this->addError($attribute, 'Must be six characters long!');
        }
    }
    function numeric($attribute) {
        $numericattribute = $this->$attribute;
        if (isset($numericattribute) && !is_numeric($numericattribute)) {
            $this->addError($attribute, 'Must be a numerical attribute!');
        }
    }

}

class BeerHop extends ActiveRecord
{
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'beer_hop';
	}

	public function relations()
	{
		return [
			'beer' => array(self::BELONGS_TO, 'Beer', 'beerId'),
			'hop' => array(self::BELONGS_TO, 'Hop', 'hopId'),
		];
	}
}

class BeerYeast extends ActiveRecord
{
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'beer_yeast';
	}

	public function relations()
	{
		return [
			'beer' => array(self::BELONGS_TO, 'Beer', 'beerId'),
			'yeast' => array(self::BELONGS_TO, 'Yeast', 'yeastId'),
		];
	}
}

class BeerMalt extends ActiveRecord
{
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'beer_malt';
	}

	public function relations()
	{
		return [
			'beer' => array(self::BELONGS_TO, 'Beer', 'beerId'),
			'malt' => array(self::BELONGS_TO, 'Malt', 'maltId'),
		];
	}
}
