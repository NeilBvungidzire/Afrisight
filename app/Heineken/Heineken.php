<?php

namespace App\Heineken;

class Heineken {

    /**
     * @param array $files
     *
     * @return GenerateOverview
     */
    public function overview(array $files)
    {
        return new GenerateOverview($files);
    }

    /**
     * @param string $identifier
     * @param string $filePath
     *
     * @return array|null
     */
    public function getData(string $identifier, string $filePath)
    {
        switch ($identifier) {
            case 'salone_consumer':
                return (new SaloneConsumer($filePath))->getData();

            case 'salone_customer':
                return (new SaloneCustomer($filePath))->getData();

            case 'trenk_consumer':
                return (new TrenkConsumer($filePath))->getData();

            case 'trenk_customer':
                return (new TrenkCustomer($filePath))->getData();
        }

        return null;
    }
}
