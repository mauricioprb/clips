import { describe, expect, it } from 'vitest';

describe('dates', () => {
  it('moves months across year boundaries', () => {
    expect(shiftMonth('2026-01', -1)).toBe('2025-12');
    expect(shiftMonth('2025-12', 1)).toBe('2026-01');
    expect(shiftMonth('2026-03', 0)).toBe('2026-03');
  });

  it('starts calendar weeks on Monday and pads both ends', () => {
    const march = Array.from({ length: 31 }, (_, index) => ({
      date: `2026-03-${String(index + 1).padStart(2, '0')}`,
    }));
    const weeks = calendarWeeks(march);

    expect(weeks).toHaveLength(6);
    expect(weeks[0]?.slice(0, 6)).toEqual([null, null, null, null, null, null]);
    expect(weeks[0]?.[6]).toEqual({ date: '2026-03-01' });
    expect(weeks[5]?.[1]).toEqual({ date: '2026-03-31' });
    expect(weeks.every((week) => week.length === 7)).toBe(true);
    expect(calendarWeeks([])).toEqual([]);
  });

  it('uses ISO weekdays and Portuguese titles without shifting the day', () => {
    expect(isoWeekday('2026-03-02')).toBe(1);
    expect(isoWeekday('2026-03-01')).toBe(7);
    expect(formatMonthTitle('2026-03')).toBe('Março de 2026');
    expect(formatDayTitle('2026-03-02')).toBe('Segunda-feira, 2 de março');
    expect(toIsoDate(new Date(2026, 11, 31, 23, 59))).toBe('2026-12-31');
  });
});

describe('time', () => {
  it.each([
    ['00:00', 0],
    ['08:30', 510],
    ['23:59', 1439],
  ])('converts %s to minutes', (clock, minutes) => {
    expect(clockToMinutes(clock)).toBe(minutes);
  });

  it.each(['8:30', '24:00', '', '12:60'])('rejects %s', (clock) => {
    expect(clockToMinutes(clock)).toBeNull();
  });

  it('pads clock times', () => {
    expect(minutesToClock(485)).toBe('08:05');
    expect(minutesToClock(1440)).toBe('24:00');
  });

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

describe('timetable', () => {
  it('shows 07:00 to 19:00 by default and grows to fit early and late slots', () => {
    expect(visibleHours([])).toEqual({ startHour: 7, endHour: 19 });
    expect(
      visibleHours([
        { startMinute: 390, endMinute: 450 },
        { startMinute: 1200, endMinute: 1290 },
      ]),
    ).toEqual({ startHour: 6, endHour: 22 });
  });

  it('places a block by start and duration', () => {
    expect(blockPosition({ startMinute: 840, endMinute: 960 }, 7)).toEqual({
      top: 7 * HOUR_HEIGHT,
      height: 2 * HOUR_HEIGHT,
    });
  });

  it('snaps a click to the half hour above it and stays inside the day', () => {
    expect(minuteAtOffset(HOUR_HEIGHT * 2 + 10, 7)).toBe(540);
    expect(minuteAtOffset(HOUR_HEIGHT * 2.6, 7)).toBe(570);
    expect(minuteAtOffset(-20, 7)).toBe(420);
    expect(minuteAtOffset(HOUR_HEIGHT * 40, 7)).toBe(1410);
  });
});
