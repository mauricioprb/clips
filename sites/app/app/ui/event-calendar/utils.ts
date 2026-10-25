import { startOfMonth, startOfWeek, type DateValue, type DayOfWeek } from '@internationalized/date';
import type { CalendarEvent } from './types';

export const toDate = (value: Date | string): Date =>
  value instanceof Date ? value : new Date(value);

export const toJsDate = (date: DateValue): Date => new Date(date.year, date.month - 1, date.day);

export const getMonthMatrix = (
  anchor: DateValue,
  locale: string,
  firstDayOfWeek?: DayOfWeek,
): DateValue[][] => {
  const weeks: DateValue[][] = [];
  let cursor = startOfWeek(startOfMonth(anchor), locale, firstDayOfWeek);
  do {
    const row: DateValue[] = [];
    for (let day = 0; day < 7; day++) {
      row.push(cursor);
      cursor = cursor.add({ days: 1 });
    }
    weeks.push(row);
  } while (cursor.month === anchor.month);
  return weeks;
};

export const eventOccursOn = (event: CalendarEvent, day: DateValue): boolean => {
  const start = toDate(event.start);
  const end = event.end ? toDate(event.end) : start;
  const target = toJsDate(day);
  const dayStart = new Date(target.getFullYear(), target.getMonth(), target.getDate());
  const dayEnd = new Date(
    target.getFullYear(),
    target.getMonth(),
    target.getDate(),
    23,
    59,
    59,
    999,
  );
  return start <= dayEnd && end >= dayStart;
};

export const getEventsForDay = (events: CalendarEvent[], day: DateValue): CalendarEvent[] =>
  events
    .filter((event) => eventOccursOn(event, day))
    .sort((a, b) => {
      if (a.allDay && !b.allDay) return -1;
      if (!a.allDay && b.allDay) return 1;
      return toDate(a.start).getTime() - toDate(b.start).getTime();
    });

export const formatTime = (date: Date, locale: string): string =>
  new Intl.DateTimeFormat(locale, {
    hour: 'numeric',
    minute: '2-digit',
  }).format(date);

export const formatWeekday = (
  date: DateValue,
  locale: string,
  weekday: 'long' | 'short' = 'long',
): string => new Intl.DateTimeFormat(locale, { weekday }).format(toJsDate(date));
