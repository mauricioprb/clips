<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\HolidayRecurrence;
use App\Models\Holiday;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final class HolidayCalendar
{
    private const array FIXED_NATIONAL = [
        '01-01' => 'Confraternização Universal',
        '04-21' => 'Tiradentes',
        '05-01' => 'Dia do Trabalho',
        '09-07' => 'Independência do Brasil',
        '10-12' => 'Nossa Senhora Aparecida',
        '11-02' => 'Finados',
        '11-15' => 'Proclamação da República',
        '11-20' => 'Dia Nacional de Zumbi e da Consciência Negra',
        '12-25' => 'Natal',
    ];

    /** @var array<int, array<string, string>> */
    private array $years = [];

    /** @param Collection<int, Holiday> $holidays */
    public function __construct(
        private readonly Collection $holidays = new Collection,
    ) {}

    public static function easter(int $year): CarbonImmutable
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $h = (19 * $a + $b - intdiv($b, 4) - intdiv($b - intdiv($b + 8, 25) + 1, 3) + 15) % 30;
        $l = (32 + 2 * ($b % 4) + 2 * intdiv($c, 4) - $h - $c % 4) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = ($h + $l - 7 * $m + 114) % 31 + 1;

        return CarbonImmutable::create($year, $month, $day);
    }

    public function nameOn(CarbonInterface $day): ?string
    {
        return $this->forYear($day->year)[$day->toDateString()] ?? null;
    }

    /** @return array<string, string> */
    public function forYear(int $year): array
    {
        return $this->years[$year] ??= $this->build($year);
    }

    /** @return array<string, string> */
    private function build(int $year): array
    {
        $easter = self::easter($year);

        $holidays = [
            $easter->subDays(47)->toDateString() => 'Carnaval',
            $easter->subDays(2)->toDateString() => 'Paixão de Cristo',
        ];

        foreach (self::FIXED_NATIONAL as $monthDay => $name) {
            $holidays["{$year}-{$monthDay}"] = $name;
        }

        foreach ($this->holidays as $holiday) {
            $date = $this->occurrence($holiday, $year, $easter);

            if ($date !== null) {
                $holidays[$date] ??= $holiday->name;
            }
        }

        ksort($holidays);

        return $holidays;
    }

    private function occurrence(Holiday $holiday, int $year, CarbonImmutable $easter): ?string
    {
        return match ($holiday->recurrence) {
            HolidayRecurrence::Once => $holiday->date->year === $year ? $holiday->date->toDateString() : null,
            HolidayRecurrence::Yearly => checkdate($holiday->date->month, $holiday->date->day, $year)
                ? sprintf('%04d-%02d-%02d', $year, $holiday->date->month, $holiday->date->day)
                : null,
            HolidayRecurrence::CorpusChristi => $easter->addDays(60)->toDateString(),
        };
    }
}
