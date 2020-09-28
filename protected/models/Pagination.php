<?php

class Pagination {
    /**
     * Here we can paginate an array and return some extra metadata like how many total pages.
     *
     * @param array $array the array of data we want to paginate.
     * @param int $pagesize
     * @param int $page
     */
    public static function paginate($array, $pagesize = PAGESIZE_DEFAULT, $page= PAGE_DEFAULT) {
        $totalcount = count($array);
        $paginatedarray = array_chunk($array, $pagesize);
        $countpages = count($paginatedarray);
        if (!isset($paginatedarray[$page - 1])) {
            return PAGE_OUT_OF_RANGE;
        }
        $resultsarray['data'] = $paginatedarray[$page - 1];
        $resultsarray['paginationdata'] = [
            'pagecount' => $countpages,
            'totalitemscount' => $totalcount,
            'pagesize' => $pagesize,
            'hasnextpage' => $countpages > $page, // Can be useful when deciding to render next button.
        ];
        return $resultsarray;
    }
}
