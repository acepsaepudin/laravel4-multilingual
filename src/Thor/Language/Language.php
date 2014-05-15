<?php

namespace Thor\Language;

/**
 * @property int $id
 * @property string $name
 * @property string $code International language code with 2 letter (ISO 639-1) or 3 letter (ISO 639-2, ISO 639-3 or ISO 639-5)
 * @property string $locale International locale code in the format [language[_territory][.codeset][@modifier]]<br> e.g. : en_US, en_AU.UTF-8
 * @property boolean $is_active
 * @property int $sorting
 */
class Language extends \Eloquent {

    /**
     *
     * @var array
     */
    protected $guarded = array();
    public static $rules = array(
        'name' => 'required',
        'code' => 'required|unique:languages'
    );

    /**
     * Current language
     * @var Language|null
     */
    protected static $current = null;

    /**
     * Returns the current resolved language code
     * @return Language|null
     */
    public static function getCurrent() {
        return self::$current;
    }

    /**
     * 
     * @param Language $current
     */
    public static function setCurrent(Language $current) {
        self::$current = $current;
    }

    /**
     * 
     * @return Language[]
     */
    public static function scopeSorted($query) {
        return $query->orderBy('sorting', 'asc');
    }

    /**
     * 
     * @return Language[]
     */
    public static function scopeActive($query) {
        return $query->where('is_active', '=', 1);
    }

    /**
     * 
     * @return Language[]
     */
    public static function scopeByCode($query, $code) {
        return $query->whereRaw('(code=?)', array($code));
    }

    /**
     * 
     * @return Language[]
     */
    public static function scopeByLocale($query, $locale) {
        return $query->whereRaw('(locale=?)', array($locale));
    }

    /**
     * 
     * @return Language[]
     */
    public static function scopeToAssoc($query) {
        $langs = $query->get();
        $langs_assoc = array();
        foreach ($langs as $lang) {
            $langs_assoc[$lang->code] = $lang;
        }
        return $langs_assoc;
    }

}