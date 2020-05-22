<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

namespace RazeSoldier\EveTranslate;

use stdClass;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ItemTranslate
{
    private $itemName;
    private $targetLang;
    private $httpClient;

    private function __construct(string $itemName, string $targetLang)
    {
        $this->itemName = $itemName;
        $this->targetLang = $targetLang;
        $this->httpClient = HttpClient::create();
    }

    /**
     * @param string $itemName
     * @param string $targetLang
     * @return string
     * @throws TranslateException
     */
    public static function translate(string $itemName, string $targetLang) : string
    {
        if ($targetLang === 'en') {
            return $itemName;
        }
        $translator = new self($itemName, $targetLang);
        return $translator->doTranslate();
    }

    /**
     * @throws TranslateException
     */
    private function doTranslate() : string
    {
        // Capsule is actually not a valid inventory type, so it can only be hardcoded
        if ($this->itemName === 'Capsule') {
            if ($this->targetLang === 'zh') {
                return '太空舱';
            } else {
                return 'Capsule';
            }
        }
        return $this->getItemInfo($this->searchItemId($this->itemName), $this->targetLang)->name;
    }

    /**
     * @param string $itemName
     * @return string The Id of the item
     * @throws TranslateException
     */
    private function searchItemId(string $itemName) : string
    {
        $url = "https://esi.evetech.net/v2/search/?categories=inventory_type&datasource=tranquility&search=$itemName&strict=true";
        return json_decode($this->sendRequest($url))->inventory_type[0];
    }

    /**
     * @param string $itemId
     * @param string $targetLang
     * @return stdClass
     * @throws TranslateException
     */
    private function getItemInfo(string $itemId, string $targetLang) : stdClass
    {
        $url = "https://esi.evetech.net/v3/universe/types/$itemId/?datasource=tranquility&language=$targetLang";
        return json_decode($this->sendRequest($url));
    }

    /**
     * @param string $url
     * @return string
     * @throws TranslateException
     */
    private function sendRequest(string $url) : string
    {
        try {
            $resp = $this->httpClient->request('GET', $url);
            if ($resp->getStatusCode() !== 200) {
                throw new TranslateException("$url response {$resp->getStatusCode()} code");
            }
        } catch (TransportExceptionInterface $e) {
            throw new TranslateException($e);
        }
        try {
            return $resp->getContent();
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface$e) {
            throw new TranslateException($e);
        }
    }
}
