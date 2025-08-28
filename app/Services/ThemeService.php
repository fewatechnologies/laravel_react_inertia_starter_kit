<?php

namespace App\Services;

use App\Models\DashboardType;
use Illuminate\Support\Collection;

class ThemeService
{
    /**
     * Get theme configuration for a dashboard type
     */
    public function getThemeConfig(string $dashboardType): array
    {
        $dashboard = DashboardType::where('type', $dashboardType)->first();
        
        if (!$dashboard) {
            return $this->getDefaultThemeConfig();
        }

        return $dashboard->theme_config;
    }

    /**
     * Update theme configuration for a dashboard type
     */
    public function updateThemeConfig(string $dashboardType, array $config): bool
    {
        $dashboard = DashboardType::where('type', $dashboardType)->first();
        
        if (!$dashboard) {
            return false;
        }

        $currentConfig = $dashboard->theme_config;
        $newConfig = array_merge($currentConfig, $config);
        
        $dashboard->theme_config = $newConfig;
        return $dashboard->save();
    }

    /**
     * Get default theme configuration
     */
    public function getDefaultThemeConfig(): array
    {
        return [
            'primary_color' => '#3b82f6',
            'secondary_color' => '#64748b',
            'sidebar_color' => '#ffffff',
            'text_color' => '#1f2937',
            'background_color' => '#f9fafb',
            'dark_mode' => false,
            'logo_url' => null,
            'favicon_url' => null,
            'font_family' => 'Inter',
            'border_radius' => '0.5rem',
            'sidebar_width' => '16rem',
        ];
    }

    /**
     * Generate CSS variables from theme config
     */
    public function generateCssVariables(array $themeConfig): string
    {
        $css = ':root {';
        
        foreach ($themeConfig as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $cssVar = '--theme-' . str_replace('_', '-', $key);
                $css .= "\n  {$cssVar}: {$value};";
            }
        }
        
        $css .= "\n}";
        
        return $css;
    }

    /**
     * Get available theme presets
     */
    public function getThemePresets(): Collection
    {
        return collect([
            'default' => [
                'name' => 'Default Blue',
                'primary_color' => '#3b82f6',
                'secondary_color' => '#64748b',
                'sidebar_color' => '#ffffff',
                'text_color' => '#1f2937',
                'background_color' => '#f9fafb',
            ],
            'dark' => [
                'name' => 'Dark Mode',
                'primary_color' => '#6366f1',
                'secondary_color' => '#9ca3af',
                'sidebar_color' => '#1f2937',
                'text_color' => '#f9fafb',
                'background_color' => '#111827',
                'dark_mode' => true,
            ],
            'green' => [
                'name' => 'Nature Green',
                'primary_color' => '#10b981',
                'secondary_color' => '#6b7280',
                'sidebar_color' => '#ffffff',
                'text_color' => '#1f2937',
                'background_color' => '#f0fdf4',
            ],
            'purple' => [
                'name' => 'Royal Purple',
                'primary_color' => '#8b5cf6',
                'secondary_color' => '#64748b',
                'sidebar_color' => '#ffffff',
                'text_color' => '#1f2937',
                'background_color' => '#faf5ff',
            ],
            'orange' => [
                'name' => 'Sunset Orange',
                'primary_color' => '#f97316',
                'secondary_color' => '#64748b',
                'sidebar_color' => '#ffffff',
                'text_color' => '#1f2937',
                'background_color' => '#fff7ed',
            ],
        ]);
    }

    /**
     * Apply theme preset to dashboard type
     */
    public function applyThemePreset(string $dashboardType, string $presetName): bool
    {
        $presets = $this->getThemePresets();
        
        if (!$presets->has($presetName)) {
            return false;
        }

        $preset = $presets->get($presetName);
        unset($preset['name']); // Remove name from config
        
        return $this->updateThemeConfig($dashboardType, $preset);
    }

    /**
     * Validate theme configuration
     */
    public function validateThemeConfig(array $config): array
    {
        $errors = [];
        
        // Validate color values
        $colorFields = ['primary_color', 'secondary_color', 'sidebar_color', 'text_color', 'background_color'];
        
        foreach ($colorFields as $field) {
            if (isset($config[$field]) && !$this->isValidColor($config[$field])) {
                $errors[$field] = 'Invalid color format. Use hex color codes (e.g., #3b82f6)';
            }
        }
        
        // Validate URLs
        $urlFields = ['logo_url', 'favicon_url'];
        
        foreach ($urlFields as $field) {
            if (isset($config[$field]) && $config[$field] && !filter_var($config[$field], FILTER_VALIDATE_URL)) {
                $errors[$field] = 'Invalid URL format';
            }
        }
        
        return $errors;
    }

    /**
     * Check if a color value is valid
     */
    protected function isValidColor(string $color): bool
    {
        // Check for hex color format
        if (preg_match('/^#[a-f0-9]{6}$/i', $color)) {
            return true;
        }
        
        // Check for rgb/rgba format
        if (preg_match('/^rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*(,\s*[\d.]+)?\s*\)$/i', $color)) {
            return true;
        }
        
        // Check for hsl/hsla format
        if (preg_match('/^hsla?\(\s*\d+\s*,\s*\d+%\s*,\s*\d+%\s*(,\s*[\d.]+)?\s*\)$/i', $color)) {
            return true;
        }
        
        return false;
    }
}