import { describe, expect, it } from 'vitest';
import { calendarWeeks, monthUrl, shiftMonth } from '@/Lib/month';
import { formatDayTitle, formatMonthTitle, isoWeekday } from '@/Lib/dates';

describe('shiftMonth', () => {
    it('moves across year boundaries', () => {
        expect(shiftMonth('2026-01', -1)).toBe('2025-12');
        expect(shiftMonth('2025-12', 1)).toBe('2026-01');
        expect(shiftMonth('2026-03', 0)).toBe('2026-03');
    });
});

describe('monthUrl', () => {
    it('builds the month route without a leading zero', () => {
        expect(monthUrl('2026-03')).toBe('/mes/2026/3');
        expect(monthUrl('2026-11', '/relatorio')).toBe('/mes/2026/11/relatorio');
    });
});

describe('calendarWeeks', () => {
    const march = Array.from({ length: 31 }, (_, index) => ({ date: `2026-03-${String(index + 1).padStart(2, '0')}` }));

    it('starts weeks on Monday and pads both ends', () => {
        const weeks = calendarWeeks(march);

        expect(weeks).toHaveLength(6);
        expect(weeks[0]?.slice(0, 6)).toEqual([null, null, null, null, null, null]);
        expect(weeks[0]?.[6]).toEqual({ date: '2026-03-01' });
        expect(weeks[5]?.[1]).toEqual({ date: '2026-03-31' });
        expect(weeks.every((week) => week.length === 7)).toBe(true);
    });

    it('returns nothing for an empty month', () => {
        expect(calendarWeeks([])).toEqual([]);
    });
});

describe('dates', () => {
    it('uses ISO weekdays', () => {
        expect(isoWeekday('2026-03-02')).toBe(1);
        expect(isoWeekday('2026-03-01')).toBe(7);
    });

    it('formats titles in Portuguese without shifting the day', () => {
        expect(formatMonthTitle('2026-03')).toBe('Março de 2026');
        expect(formatDayTitle('2026-03-02')).toBe('Segunda-feira, 2 de março');
    });
});

describe('local date conversion', () => {
    it('round trips without timezone drift', async () => {
        const { fromIsoDate, toIsoDate } = await import('@/Lib/dates');

        expect(toIsoDate(fromIsoDate('2026-03-01'))).toBe('2026-03-01');
        expect(toIsoDate(new Date(2026, 11, 31, 23, 59))).toBe('2026-12-31');
    });
});
