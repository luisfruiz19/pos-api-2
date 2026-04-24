<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySettings extends Model
{
    protected $fillable = [
        'nombre',
        'ruc',
        'direccion',
        'telefono',
        'email',
        'logo_path',
        'receipt_footer',
        'igv_rate',
        'igv_enabled',
        'currency',
        'palette',
    ];

    protected $casts = [
        'igv_rate'    => 'decimal:2',
        'igv_enabled' => 'boolean',
        'palette'     => 'array',
    ];

    public static function defaultPalette(): array
    {
        return [
            // Primary
            'primary'                  => '#415a89',
            'primaryContainer'         => '#5a73a3',
            'primaryFixed'             => '#d7e2ff',
            'primaryFixedDim'          => '#adc7fc',
            'onPrimary'                => '#ffffff',
            'onPrimaryFixed'           => '#001a40',
            'onPrimaryFixedVariant'    => '#2d4674',
            'inversePrimary'           => '#adc7fc',
            // Secondary
            'secondary'                => '#006491',
            'secondaryContainer'       => '#55bcfd',
            'secondaryFixed'           => '#c9e6ff',
            'secondaryFixedDim'        => '#8aceff',
            'onSecondary'              => '#ffffff',
            'onSecondaryFixed'         => '#001e2f',
            'onSecondaryFixedVariant'  => '#004b6f',
            'onSecondaryContainer'     => '#004a6d',
            // Tertiary
            'tertiary'                 => '#6d5e00',
            'tertiaryContainer'        => '#c4aa01',
            'tertiaryFixed'            => '#ffe24c',
            'tertiaryFixedDim'         => '#e2c62d',
            'onTertiary'               => '#ffffff',
            'onTertiaryContainer'      => '#4a3f00',
            'onTertiaryFixed'          => '#211b00',
            'onTertiaryFixedVariant'   => '#524600',
            // Error
            'error'                    => '#ba1a1a',
            'errorContainer'           => '#ffdad6',
            'onError'                  => '#ffffff',
            'onErrorContainer'         => '#93000a',
            // Surface
            'surface'                  => '#f8f9fb',
            'surfaceBright'            => '#f8f9fb',
            'surfaceDim'               => '#d9dadc',
            'surfaceVariant'           => '#e1e2e4',
            'surfaceContainer'         => '#edeef0',
            'surfaceContainerLow'      => '#f3f4f6',
            'surfaceContainerHigh'     => '#e7e8ea',
            'surfaceContainerLowest'   => '#ffffff',
            'surfaceContainerHighest'  => '#e1e2e4',
            'surfaceTint'              => '#455e8d',
            // On-Surface
            'onSurface'                => '#191c1e',
            'onSurfaceVariant'         => '#44474f',
            'onBackground'             => '#191c1e',
            'inverseSurface'           => '#2e3132',
            'inverseOnSurface'         => '#f0f1f3',
            // Neutral
            'outline'                  => '#747780',
            'outlineVariant'           => '#c4c6d0',
            'background'               => '#f8f9fb',
            // Gradients
            'gradientFrom'             => '#415a89',
            'gradientTo'               => '#5a73a3',
            'gradientHoverFrom'        => '#4d6a9e',
            'gradientHoverTo'          => '#6884b5',
        ];
    }

    // Siempre devuelve el único registro, creándolo si no existe
    public static function getInstance(): static
    {
        return static::firstOrCreate(
            ['id' => 1],
            ['palette' => static::defaultPalette()]
        );
    }

    // Devuelve la paleta fusionada con los defaults (por si faltan tokens nuevos)
    public function getResolvedPaletteAttribute(): array
    {
        return array_merge(static::defaultPalette(), $this->palette ?? []);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path
            ? asset('storage/' . $this->logo_path)
            : null;
    }
}
