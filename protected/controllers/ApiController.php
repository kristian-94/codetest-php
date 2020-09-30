<?php

class ApiController extends CController
{
    // Actions
    public function actionFetch()
    {
        // Get a search query and other pagination params if they exist.
        $query = isset($_GET['query']) ? $_GET['query'] : '';
        $pagesize = isset($_GET['pagesize']) ? $_GET['pagesize'] : PAGESIZE_DEFAULT;
        $page = isset($_GET['page']) ? $_GET['page'] : PAGE_DEFAULT;

        $paramscheck = Pagination::checkPageParams($pagesize, $page);
        if ($paramscheck !== false) {
            $this->_sendResponse(400, "Incorrect params given: \n" . $paramscheck);
        }

        $beers = Beer::model()->findBeersByName($query, $pagesize, $page);
        if ($beers === NO_BEER_FOUND) {
            // 406 Not Acceptable after performing server-driven content negotiation, doesn't find any content that conforms to the criteria.
            $this->_sendResponse(406, 'No beers found matching your query');
        } else if ($beers === PAGE_OUT_OF_RANGE) {
            // 406 Not Acceptable for page being outside the range - the server cannot produce a matching response.
            $this->_sendResponse(406, 'Found beers but your page was out of range');
        }
        $this->_sendResponse(200, CJSON::encode($beers));
    }
    public function actionUpdate()
    {
        $beerid = isset($_POST['beerId']) ? $_POST['beerId'] : null;
        if (!isset($beerid)) {
            $this->_sendResponse(400, "beerId is required to update a beer");
        }
        $beer = Beer::model()->findWithBeerId($beerid);
        $beer->setAttributes($_POST);
        $beer->validate();
        $errors = $beer->getErrors();
        if ($errors) {
            $errormessage = "Invalid beer data given! \n";
            foreach ($errors as $attribute => $error) {
                $errormessage .= "$attribute is invalid: $error[0]\n";
            }
            $this->_sendResponse(400, $errormessage);
        }
        $success = $beer->save();
        if ($success === false) {
            $this->_sendResponse(500, 'Failed to update beer with name ' . $beer->getAttribute('name'));
        }
        $this->_sendResponse(200, 'Successfully update beer with beerId "' . $beerid . '"');
    }
    private function _sendResponse($status = 200, $body = '', $content_type = 'application/json')
    {
        // set the status
        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        header($status_header);
        // and the content type
        header('Content-type: ' . $content_type);

        // pages with body are easy
        if($body != '')
        {
            // send the body
            echo $body;
        }
        // we need to create the body if none is passed
        else
        {
            // create some body messages
            $message = '';

            // this is purely optional, but makes the pages a little nicer to read
            // for your users.  Since you won't likely send a lot of different status codes,
            // this also shouldn't be too ponderous to maintain
            switch($status)
            {
                case 406:
                    $message = 'Found beers but your requested page was outside the range.';
                    break;
                case 400:
                    $message = 'Your request params are malformed or otherwise incorrect.';
                    break;
                case 401:
                    $message = 'You must be authorized to view this page.';
                    break;
                case 404:
                    $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }
            $body = 'Got status "' . $status . ': ' . $this->_getStatusCodeMessage($status) . '". ' . $message;
            echo $body;
        }
        Yii::app()->end();
    }
    private function _getStatusCodeMessage($status)
    {
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = Array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            406 => 'Not Acceptable',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }
}
