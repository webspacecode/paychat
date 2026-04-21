<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'setting_key',
        'value',
        'type'
    ];

    /**
     * Get setting value by key
     */
    public static function get($key, $tenantId = null, $default = null)
    {

        $setting = self::query()
            ->where('setting_key', $key)
            ->first();

        return $setting ? self::castValue($setting) : $default;
    }

    /**
     * Set / update setting
     */
    public static function set($key, $value, $type = 'string', $tenantId = null)
    {

        return self::updateOrCreate(
            [
                'setting_key' => $key
            ],
            [
                'value' => self::prepareValue($value, $type),
                'type' => $type
            ]
        );
    }

    /**
     * Cast DB value to proper type
     */
    private static function castValue($setting)
    {
        switch ($setting->type) {
            case 'boolean':
                return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);

            case 'json':
                return json_decode($setting->value, true);

            case 'integer':
                return (int) $setting->value;

            case 'float':
                return (float) $setting->value;

            default:
                return $setting->value;
        }
    }

    /**
     * Prepare value before saving
     */
    private static function prepareValue($value, $type)
    {
        switch ($type) {
            case 'json':
                return json_encode($value);

            case 'boolean':
                return $value ? '1' : '0';

            default:
                return $value;
        }
    }
}