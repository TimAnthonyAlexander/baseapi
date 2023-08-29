<?php

namespace timanthonyalexander\BaseApi\model\Translation;

use timanthonyalexander\BaseApi\module\EnvService\EnvService;
use timanthonyalexander\BaseApi\module\InstantCache\InstantCache;
use timanthonyalexander\BaseApi\module\TranslationConfig\TranslationConfig;
use timanthonyalexander\BaseApi\module\UserState\UserState;

class TranslationModel extends TranslationConfig
{
    public string $id;
    public string $english = '';
    public string $german = '';
    public string $french = '';
    public string $spanish = '';
    public string $italian = '';
    public string $dutch = '';
    public string $portuguese = '';

    public function translate(
        string $token,
        bool $ucFirst = true,
        string $languageOverride = null
    ): string {

        $arrayData = $this->toArray();

        unset($arrayData['config']);

        $language = $languageOverride ?? (UserState::isLogin() ? UserState::getState()->userModel->language : 'english');

        if (EnvService::isDev()) {
            $translation = $this->getConfigItem($token, $arrayData)[$language] ?? $token;
        } else {
            $translation = $this->getConfigItem($token)[$language] ?? $token;
        }

        if ($ucFirst) {
            $translation = ucfirst((string) $translation);
        }

        if ($translation === '') {
            $translation = $token;
        }


        return $translation;
    }

    public static function getTranslation(
        string $id,
        bool $ucFirst = true,
        string $forceLanguage = null
    ): string {

        if (!InstantCache::isset('translationmodel')) {
            InstantCache::set('translationmodel', new TranslationModel());
        }


        $translationModel = InstantCache::get('translationmodel');



        assert($translationModel instanceof self);
        $arrayData = $translationModel->toArray();
        unset($arrayData['config']);



        $language = $forceLanguage ?? (UserState::isLogin() ? UserState::getState()->userModel->language : 'english');



        $translation = $translationModel->getConfigItem($id, $arrayData)[$language] ?? $id;



        if ($ucFirst) {
            $translation = ucfirst((string) $translation);
        }



        if ($translation === '') {
            $translation = $id;
        }



        return $translation;
    }

    public static function getLanguageForLocalName(string $localName): string
    {
        $localName = trim(strtolower($localName));

        $english = ['english', 'en', 'en-us', 'en-gb', 'englisch', 'anglais', 'inglés', 'inglese', 'engels', 'inglês', 'engelsk'];
        $german = ['german', 'de', 'de-de', 'de-ch', 'de-at', 'deutsch', 'allemand', 'alemán', 'tedesco', 'duits', 'alemão', 'tysk'];
        $french = ['french', 'fr', 'fr-fr', 'fr-be', 'fr-ca', 'fr-ch', 'français', 'francese', 'frans', 'francês', 'francais', 'fransk'];
        $spanish = ['spanish', 'es', 'es-es', 'es-ar', 'es-cl', 'es-mx', 'es-co', 'es-pe', 'es-ve', 'es-cr', 'es-do', 'es-ec', 'es-gt', 'es-hn', 'es-ni', 'es-pa', 'es-py', 'es-sv', 'es-uy', 'es-bo', 'es-sv', 'es-gt', 'es-hn', 'es-ni', 'es-pa', 'es-py', 'es-sv', 'es-uy', 'es-bo', 'es-par', 'es-ec', 'es-us', 'es-cl', 'es-mx', 'es-pe', 'es-co', 'es-ve', 'es-cr', 'es-do', 'es-es', 'es-419', 'es', 'espagnol', 'spanisch', 'spaans'];
        $italian = ['italian', 'it', 'it-it', 'it-ch', 'italienisch', 'italiano', 'italien', 'italiano', 'italiaans', 'italiano', 'italiensk'];
        $dutch = ['dutch', 'nl', 'nl-nl', 'nl-be', 'niederländisch', 'néerlandais', 'neerlandés', 'olandese', 'nederlands', 'holandês', 'nederlandsk'];
        $portuguese = ['portuguese', 'pt', 'pt-br', 'pt-pt', 'portugais', 'portugués', 'portoghese', 'portugees', 'português', 'portugisisk'];

        if (in_array($localName, $english, true)) {
            return 'english';
        }

        if (in_array($localName, $german, true)) {
            return 'german';
        }

        if (in_array($localName, $french, true)) {
            return 'french';
        }

        if (in_array($localName, $spanish, true)) {
            return 'spanish';
        }

        if (in_array($localName, $italian, true)) {
            return 'italian';
        }

        if (in_array($localName, $dutch, true)) {
            return 'dutch';
        }

        if (in_array($localName, $portuguese, true)) {
            return 'portuguese';
        }

        return 'german';
    }
}
