<?php

namespace Mjolnic\Language;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $locale
 * @property boolean $is_active
 * @property int $sorting
 */
class Language extends \Illuminate\Database\Eloquent {

    /**
     *
     * @var array
     */
    protected $guarded = array();
    public static $rules = array(
        'name' => 'required',
        'code' => 'required|unique:languages'
    );

    public static function scopeSorted($query) {
        return $query->orderBy('sorting', 'asc');
    }

    public static function scopeActive($query) {
        return $query->where('is_active', '=', 1);
    }

    public static function scopeByCode($query, $code) {
        return $query->whereRaw('(code=?)', array($code));
    }

    /**
     * 
     * @return \Model\Lang[]
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
