import { isoWeekday } from '@/Lib/dates';

export function shiftMonth(month: string, offset: number): string {
    const [year, monthNumber] = month.split('-').map(Number);
    const date = new Date(Date.UTC(year ?? 1970, (monthNumber ?? 1) - 1 + offset, 1));

    return `${date.getUTCFullYear()}-${String(date.getUTCMonth() + 1).padStart(2, '0')}`;
}

export function monthUrl(month: string, suffix = ''): string {
    const [year, monthNumber] = month.split('-');

    return `/mes/${year}/${Number(monthNumber)}${suffix}`;
}

export function calendarWeeks<Day extends { date: string }>(days: Day[]): (Day | null)[][] {
    const first = days[0];

    if (!first) {
        return [];
    }

    const cells: (Day | null)[] = [...Array<null>(isoWeekday(first.date) - 1).fill(null), ...days];

    while (cells.length % 7 !== 0) {
        cells.push(null);
    }

    return Array.from({ length: cells.length / 7 }, (_, week) => cells.slice(week * 7, week * 7 + 7));
}
