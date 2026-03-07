<?php

namespace App\Services;

use Carbon\Carbon;

class DateTimeFormatter
{
    public function __construct(
        private SchoolProfileService $schoolProfileService
    ) {}

    /**
     * Format a date-only value (no timezone conversion). Returns null for null input.
     */
    public function formatDate(Carbon|\DateTimeInterface|string|null $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = $value instanceof Carbon ? $value : Carbon::parse($value);
        $format = $this->getDateFormat();

        return $date->format($this->carbonToPhpFormat($format));
    }

    /**
     * Format a datetime value (timezone applied). Returns null for null input.
     */
    public function formatDateTime(Carbon|\DateTimeInterface|string|null $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = $value instanceof Carbon ? $value : Carbon::parse($value);
        $settings = $this->resolveSettings();
        $tz = $settings['timezone'] ?? config('app.timezone');
        $date = $date->timezone($tz);

        $dateFormat = $this->carbonToPhpFormat($settings['date_format'] ?? 'Y-m-d');
        $timeFormat = $this->carbonToPhpFormat($settings['time_format'] ?? 'H:i');

        return $date->format($dateFormat.' '.$timeFormat);
    }

    /**
     * @return array{date_format: string, time_format: string, timezone: string}
     */
    private function resolveSettings(): array
    {
        $school = $this->schoolProfileService->getDefaultSchool();
        $settings = $school?->settings;

        return [
            'date_format' => $settings?->date_format ?? 'Y-m-d',
            'time_format' => $settings?->time_format ?? 'H:i',
            'timezone' => $settings?->timezone ?? config('app.timezone'),
        ];
    }

    private function getDateFormat(): string
    {
        $settings = $this->resolveSettings();

        return $settings['date_format'];
    }

    /**
     * Carbon/PHP date format uses same tokens (d, m, Y, H, i, etc.). Pass through.
     */
    private function carbonToPhpFormat(string $format): string
    {
        return $format;
    }
}
