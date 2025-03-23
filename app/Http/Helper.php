<?php
namespace App\Http;

class Helper
{
    public static function plainContent($content) {
        $decodedContent = html_entity_decode($content);
        $plainText = strip_tags($decodedContent);
        return $plainText;
    }
}