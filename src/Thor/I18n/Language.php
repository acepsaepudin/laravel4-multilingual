<?php

namespace Thor\I18n;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $fullcode
 * @property string $locale
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
    public static function scopeToAssoc($query) {
        $langs = $query->get();
        $langs_assoc = array();
        foreach ($langs as $lang) {
            $langs_assoc[$lang->code] = $lang;
        }
        return $langs_assoc;
    }

}