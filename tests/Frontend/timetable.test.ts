import { describe, expect, it } from 'vitest';
import { blockPosition, HOUR_HEIGHT, minuteAtOffset, visibleHours } from '@/Lib/timetable';

describe('visibleHours', () => {
    it('shows 07:00 to 19:00 by default', () => {
        expect(visibleHours([])).toEqual({ startHour: 7, endHour: 19 });
    });

    it('extends to fit early and late ranges', () => {
        expect(
            visibleHours([
                { startMinute: 390, endMinute: 450 },
                { startMinute: 1200, endMinute: 1290 },
            ]),
        ).toEqual({
            startHour: 6,
            endHour: 22,
        });
    });
});

describe('blockPosition', () => {
    it('places a block by start and duration', () => {
        expect(blockPosition({ startMinute: 840, endMinute: 960 }, 7)).toEqual({
            top: 7 * HOUR_HEIGHT,
            height: 2 * HOUR_HEIGHT,
        });
    });
});

describe('minuteAtOffset', () => {
    it('snaps a click to the half hour above it', () => {
        expect(minuteAtOffset(HOUR_HEIGHT * 2 + 10, 7)).toBe(540);
        expect(minuteAtOffset(HOUR_HEIGHT * 2.6, 7)).toBe(570);
    });

    it('stays inside the day', () => {
        expect(minuteAtOffset(-20, 7)).toBe(420);
        expect(minuteAtOffset(HOUR_HEIGHT * 40, 7)).toBe(1410);
    });
});
