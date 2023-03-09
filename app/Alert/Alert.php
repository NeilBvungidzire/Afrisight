<?php

namespace App\Alert;

use App\Alert\Exceptions\UnknownAlertTypeMethod;

class Alert {

    /**
     * @var array
     */
    private $types = [
        'primary',
        'secondary',
        'success',
        'danger',
        'warning',
        'info',
        'light',
        'dark',
    ];

    /**
     * @var int
     */
    private $alertIndex = 0;

    /**
     * @var array
     */
    private $flash = [];

    /**
     * @var string
     */
    private $type = 'info';

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return Alert
     * @throws UnknownAlertTypeMethod
     */
    public function __call($name, $arguments)
    {
        $type = strtolower(substr($name, 4));
        if ( ! in_array($type, $this->types)) {
            throw new UnknownAlertTypeMethod('Method for type "' . $type . '" does not exist.');
        }

        $this->type = $type;

        return $this->makeAlert(...$arguments);
    }

    /**
     * @param string|null $body
     *
     * @return Alert
     */
    private function makeAlert(string $body = null)
    {
        $allAlerts = session('alert');

        // The first time no session is set for alert.
        if ( ! empty($allAlerts) && is_array($allAlerts)) {
            $lastAlertIndex = (int)array_key_last($allAlerts);
            $this->alertIndex = $lastAlertIndex + 1;
        }

        $flash = [
            'type'    => $this->type,
            'body'    => $body,
            'heading' => null,
        ];

        $this->setFlash($flash);

        return $this;
    }

    /**
     * @param array $parts
     */
    private function setFlash(array $parts)
    {
        $this->flash = array_merge($this->flash, $parts);

        session()->flash('alert.' . $this->alertIndex, $this->flash);
    }

    /**
     * @param string $text
     *
     * @return $this
     */
    public function setBody(string $text)
    {
        $flash['body'] = $text;

        $this->setFlash($flash);

        return $this;
    }

    /**
     * @param string $text
     *
     * @return Alert
     */
    public function setHeading(string $text)
    {
        $flash['heading'] = $text;

        $this->setFlash($flash);

        return $this;
    }
}
