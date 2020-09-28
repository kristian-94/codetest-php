<?php
const PAGE_OUT_OF_RANGE = 'page out of range';

/**
 * Class BeerViewModel represents a model of the view of the beers data, including all tables.
 */
class BeerViewModel
{
    /**
     * Here we search all the related beers data fields that contain text for our search query.
     * If the query is found, we return all data found for that beer.
     *
     * @param string $searchquery the search query.
     */
    public static function search($searchquery = null)
    {

        $sqlquery = Yii::app()->db->createCommand()
                                     ->select([
                                                  'b.beerId as beerId',
                                                  'b.name as beername',
                                                  's.name as stylename',
                                                  's.description as styledesc',
                                                  'h.hopId as hopId',
                                                  'h.name as hopname',
                                                  'h.description as hopdesc',
                                                  'y.yeastId as yeastId',
                                                  'y.name as yeastname',
                                                  'y.description as yeastdesc',
                                                  'm.maltId as maltId',
                                                  'm.name as maltname',
                                                  'm.description as maltdesc',
                                              ])
                                     ->from('Beer b')
                                     ->join('beer_hop bh', 'b.beerId = bh.beerId')
                                     ->join('beer_malt bm', 'b.beerId = bm.beerId')
                                     ->join('beer_yeast by', 'b.beerId = by.beerId')
                                     ->join('Malt m', 'm.maltId = bm.maltId')
                                     ->join('Hop h', 'h.hopId = bh.hopId')
                                     ->join('Yeast y', 'y.yeastId = by.yeastId')
                                     ->join('style s', 'b.styleId=s.styleId')
                                     ->order('b.name');

        if ($searchquery) {
            // TODO check if this is vulnerable to SQL injection, must be a proper yii way to do this?
            $sqlquery->orWhere("b.name LIKE '%$searchquery%'")
                ->orWhere("h.name LIKE '%$searchquery%'")
                ->orWhere("h.description LIKE '%$searchquery%'")
                ->orWhere("m.name LIKE '%$searchquery%'")
                ->orWhere("m.description LIKE '%$searchquery%'")
                ->orWhere("s.name LIKE '%$searchquery%'")
                ->orWhere("s.description LIKE '%$searchquery%'")
                ->orWhere("y.name LIKE '%$searchquery%'")
                ->orWhere("y.description LIKE '%$searchquery%'");
        }


        $test = $sqlquery->query();
        $rawbeers = $test->readAll();

        $beers = [];

        $types = ['malt', 'hop', 'yeast'];
        // We can have different malt, hop, yeast data for the same beer. These are returned inside of the individual beer json object.
        foreach ($rawbeers as $rawbeer) {
            $beerid = $rawbeer['beerId'];
            foreach ($types as $type) {
                $id = $type . 'Id';
                $name = $type . 'name';
                $desc = $type . 'desc';
                $data = $type . 'data';
                if (!isset($beers[$beerid])) {
                    $maltdata[$rawbeer[$id]] = [
                        $rawbeer[$id],
                        $rawbeer[$name],
                        $rawbeer[$desc],
                    ];
                    $rawbeer[$data] = $maltdata;
                    unset($rawbeer[$id]);
                    unset($rawbeer[$name]);
                    unset($rawbeer[$desc]);
                } else {
                    $newId = $rawbeer[$id];
                    // This beer already is added. So we want to add the malt, hop, yeast data if it's different.
                    if (!isset($beers[$beerid][$data][$newId])) {
                        $beers[$beerid][$data][$newId] = [
                            $rawbeer[$id],
                            $rawbeer[$name],
                            $rawbeer[$desc],
                        ];
                    }
                }
            }
            if (!isset($beers[$beerid])) {
                $beers[$beerid] = $rawbeer;
            }
        }
        return $beers;
    }
}
