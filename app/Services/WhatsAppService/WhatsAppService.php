<?php

namespace App\Services\WhatsAppService;

class WhatsAppService {

    public static function getUniversalLink(string $message = null): string
    {
        $message = urlencode($message);
        return "https://wa.me/?text=${message}";
    }
}
