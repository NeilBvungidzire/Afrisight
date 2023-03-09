<?php

namespace App\Cint;

use Exception;

trait GeneralResourceApi {

    /**
     * @return $this
     */
    public function generalResources()
    {
        try {
            return $this->from('/');
        } catch (Exception $exception) {
            $this->setStates(0);
            report($exception);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function retrieveGenders()
    {
        try {
            return $this->follow('panelist/genders');
        } catch (Exception $exception) {
            $this->setStates(0);
            report($exception);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function retrieveCountries()
    {
        try {
            return $this->follow('countries');
        } catch (Exception $exception) {
            $this->setStates(0);
            report($exception);
        }

        return $this;
    }
}
