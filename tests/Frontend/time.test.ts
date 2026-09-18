import { describe, expect, it } from 'vitest';
import { clockToMinutes, formatDuration, minutesToClock } from '@/Lib/time';

describe('clockToMinutes', () => {
    it.each([
        ['00:00', 0],
        ['08:30', 510],
        ['23:59', 1439],
    ])('converts %s', (clock, minutes) => {
        expect(clockToMinutes(clock)).toBe(minutes);
    });

    it.each(['8:30', '24:00', '', '12:60'])('rejects %s', (clock) => {
        expect(clockToMinutes(clock)).toBeNull();
    });
});

describe('minutesToClock', () => {
    it('pads hours and minutes', () => {
        expect(minutesToClock(485)).toBe('08:05');
        expect(minutesToClock(1440)).toBe('24:00');
    });
});

describe('formatDuration', () => {
    it.each([
        [0, '0h'],
        [30, '30min'],
        [240, '4h'],
        [270, '4h30'],
        [605, '10h05'],
    ])('formats %i minutes as %s', (minutes, expected) => {
        expect(formatDuration(minutes)).toBe(expected);
    });
});
