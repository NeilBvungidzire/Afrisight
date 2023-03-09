<?php

namespace App\Libraries\Flutterwave;

use App\Libraries\Flutterwave\Utils\Banks;
use App\Libraries\Flutterwave\Utils\Miscellaneous;
use App\Libraries\Flutterwave\Utils\Transfers;

class Flutterwave {

    /**
     * @return Miscellaneous
     */
    public function miscellaneous()
    {
        return new Miscellaneous();
    }

    /**
     * @return Transfers
     */
    public function transfers()
    {
        return new Transfers();
    }

    /**
     * @return Banks
     */
    public function banks()
    {
        return new Banks();
    }
}
